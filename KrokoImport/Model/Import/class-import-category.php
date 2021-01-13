<?php

namespace KrokoImport\Model\Import;

use KrokoImport\Exceptions\Exception;
use KrokoImport\Exceptions\Wp_Category_Not_Found_Exception;

require_once ABSPATH . 'wp-admin/includes/taxonomy.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

class Import_Category extends Loggable
{

    const WP_CATEGORY_META_KEY_ID = 'kroko_import_category_id';

    public function get_wp_categories_by_xml_category_key(string $category_key): array
    {
        // попытаться получить категорию с таким id(из XML)
        $categories = get_categories([
            'meta_key' => self::WP_CATEGORY_META_KEY_ID,
            'meta_value' => $category_key,
        ]);
        if (empty($categories)) {
            throw new Wp_Category_Not_Found_Exception;
        }
        return $categories;
    }

    public function insert(string $category_key, string $category_value): int
    {
        $args = array(
            'cat_name' => $category_value,
        );
        $id = $this->insert_db($args, $category_key);
        $this->_logs[] = 'Вставлена категория: ' . $category_key . ' - "' . $category_value . '"';
        return $id;
    }

    public function update($wp_category, string $category_value): int
    {
        $args = array(
            'cat_ID' => $wp_category->ID,
            'cat_name' => $category_value,
        );
        $id = $this->insert_db($args, null);
        $this->_logs[] = 'Обновлена категория: "' . $category_value . '", wp id: ' . $wp_category->cat_ID;
        return $id;
    }

    private function insert_db(array $args, ?string $category_key): int
    {
        // вставить или изменить категорию
        $insert_id = wp_insert_category($args, true);
        if (isset($insert_id->error_data['term_exists'])) {
            $insert_id = $insert_id->error_data['term_exists'];
        } else if (is_numeric($insert_id)) {
            // при апдейте было бы и так совпадение, при вставке нужно добавить к категории наш id из xml
            if (is_null($category_key)) {
                throw new Exception('Нету $categoryKey');
            }
            add_term_meta($insert_id, self::WP_CATEGORY_META_KEY_ID, $category_key, true);
        } else {
            throw new Exception('wp_insert_category response error');
        }
        return $insert_id;
    }

}
