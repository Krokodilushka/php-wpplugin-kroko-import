<?php

namespace KrokoImport\Model;

use KrokoImport\Data\Xml\Comment;
use KrokoImport\Data\Xml\Comments;
use KrokoImport\Data\Xml\Feed;
use KrokoImport\Data\Xml\Key_Value;
use KrokoImport\Data\Xml\Key_Value_Storage;
use KrokoImport\Data\Xml\Post;
use KrokoImport\Data\Xml\Strings_Storage;
use KrokoImport\Exceptions\XML_Parser_Exception;

class XML_Parser
{

    static function load($url)
    {
        return simplexml_load_file($url, 'SimpleXMLElement', LIBXML_NOWARNING);
    }

    static function parse($simpleXml)
    {
        if (!isset($simpleXml->post)) {
            throw new XML_Parser_Exception("посты не найдены");
        }
        $feed = new Feed();
        $itemPos = 0;
        foreach ($simpleXml->post as $post) {
            if (!isset($post->id)) {
                throw new XML_Parser_Exception("id не найден в post $itemPos");
            }
            if (!isset($post->title)) {
                throw new XML_Parser_Exception("title не найден в post $itemPos");
            }
            $id = $post->id;
            $title = $post->title;
            $slug = NULL;
            if (isset($post->slug)) {
                $slug = $post->slug;
            }
            $thumbnail = ($post->thumbnail) ? $post->thumbnail : '';
            $content = ($post->content) ? $post->content : '';
            $date = (new \DateTime())->setTimestamp((string)$post->date);
            // категории
            $categories = new Key_Value_Storage();
            if (isset($post->category)) {
                $catPos = 0;
                foreach ($post->category as $value) {
                    if (!isset($value->id) || !isset($value->value)) {
                        throw new XML_Parser_Exception("в категории нет id или value. post ID $id, категория #" . $catPos);
                    }
                    $categories->put(new Key_Value((string)$value->id, (string)$value->value));
                    $catPos++;
                }
            }
            $tags = new Strings_Storage();
            // теги 
            if (isset($post->tag)) {
                $tagPos = 0;
                foreach ($post->tag as $value) {
                    $tags->put((string)$value);
                    $tagPos++;
                }
            }
            $metas = new Key_Value_Storage();
            // мета 
            if (isset($post->meta)) {
                $metaPos = 0;
                foreach ($post->meta as $value) {
                    if (!isset($value->key) || !isset($value->value)) {
                        throw new XML_Parser_Exception("в мета нет key или value. post ID $id, meta #" . $metaPos);
                    }
                    $metas->put(new Key_Value((string)$value->key, (string)$value->value));
                    $metaPos++;
                }
            }
            // комментарии
            $comments = new Comments();
            self::processComments($comments, $post);
            $feed->putPost(new Post(
                (string)$id,
                (string)$title,
                $slug,
                (string)$thumbnail,
                $date,
                (string)$content,
                $categories,
                $metas,
                $tags,
                $comments
            ));
            $itemPos++;
        }
        return $feed;
    }

    static function processComments($comments, $simpleXMLElement)
    {
        if (isset($simpleXMLElement->comment)) {
            foreach ($simpleXMLElement->comment as $comment) {
                if (!isset($comment->id)) {
                    throw new XML_Parser_Exception("у комментария должен быть guid");
                }
                if (!isset($comment->author)) {
                    throw new XML_Parser_Exception("у комментария должен быть author. comment id" . $comment->id);
                }
                if (!isset($comment->date) || !is_numeric((string)$comment->date)) {
                    throw new XML_Parser_Exception("у комментария должен быть date и это должно быть число. comment id" . $comment->id);
                }
                if (!isset($comment->text)) {
                    throw new XML_Parser_Exception("у комментария должен быть text. comment id" . $comment->id);
                }
                $metas = new Key_Value_Storage();
                // мета 
                if (isset($comment->meta)) {
                    $metaPos = 0;
                    foreach ($comment->meta as $value) {
                        if (!isset($value->key) || !isset($value->value)) {
                            throw new XML_Parser_Exception("в мета комментария нет key или value. comment xml ID $comment->id, meta #" . $metaPos);
                        }
                        $metas->put(new Key_Value((string)$value->key, (string)$value->value));
                        $metaPos++;
                    }
                }
                $replies = new Comments();
                if (isset($comment->replies)) {
                    self::processComments($replies, $comment->replies);
                }
                $comments->put(new Comment((string)$comment->id, (string)$comment->author, (new \DateTime())->setTimestamp((int)$comment->date), (string)$comment->text, $metas, $replies));
            }
        }
    }

}
