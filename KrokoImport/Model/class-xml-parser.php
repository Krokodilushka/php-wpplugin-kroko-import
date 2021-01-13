<?php

namespace KrokoImport\Model;

use DateTime;
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

    static function parse($simple_xml)
    {
        if (!isset($simple_xml->post)) {
            throw new XML_Parser_Exception("посты не найдены");
        }
        $feed = new Feed();
        $item_pos = 0;
        foreach ($simple_xml->post as $post) {
            if (!isset($post->id)) {
                throw new XML_Parser_Exception("id не найден в post $item_pos");
            }
            if (!isset($post->title)) {
                throw new XML_Parser_Exception("title не найден в post $item_pos");
            }
            $id = $post->id;
            $title = $post->title;
            $slug = NULL;
            if (isset($post->slug)) {
                $slug = $post->slug;
            }
            $thumbnail = ($post->thumbnail) ? $post->thumbnail : '';
            $content = ($post->content) ? $post->content : '';
            $date = (new DateTime())->setTimestamp((string)$post->date);
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
            self::process_comments($comments, $post);
            $feed->put_post(new Post(
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
            $item_pos++;
        }
        return $feed;
    }

    static function process_comments($comments, $simple_xml_element)
    {
        if (isset($simple_xml_element->comment)) {
            foreach ($simple_xml_element->comment as $comment) {
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
                    self::process_comments($replies, $comment->replies);
                }
                $comments->put(new Comment((string)$comment->id, (string)$comment->author, (new DateTime())->setTimestamp((int)$comment->date), (string)$comment->text, $metas, $replies));
            }
        }
    }

}
