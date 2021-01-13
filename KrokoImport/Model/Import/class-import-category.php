<?php

namespace KrokoImport\Model\Import;

use KrokoImport\Exceptions\Exception;
use KrokoImport\Exceptions\WpCategoryNotFoundException;

require_once ABSPATH . 'wp-admin/includes/taxonomy.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

class Import_Category extends Loggable
{

    const WP_CATEGORY_META_KEY_ID = 'kroko_import_category_id';

    public function getWPCategoriesByXMLCategoryKey(string $categoryKey): array
    {
        // попытаться получить категорию с таким id(из XML)
        $categories = get_categories([
            'meta_key' => self::WP_CATEGORY_META_KEY_ID,
            'meta_value' => $categoryKey,
        ]);
        if (empty($categories)) {
            throw new WpCategoryNotFoundException;
        }
        return $categories;
    }

    public function insert(string $categoryKey, string $categoryValue): int
    {
        $args = array(
            'cat_name' => $categoryValue,
        );
        $id = $this->insertDb($args, $categoryKey);
        $this->_logs[] = 'Вставлена категория: ' . $categoryKey . ' - "' . $categoryValue . '"';
        return $id;
    }

    public function update($wpCategory, string $categoryValue): int
    {
        $args = array(
            'cat_ID' => $wpCategory->ID,
            'cat_name' => $categoryValue,
        );
        $id = $this->insertDb($args, null);
        $this->_logs[] = 'Обновлена категория: "' . $categoryValue . '", wp id: ' . $wpCategory->cat_ID;
        return $id;
    }

    private function insertDb(array $args, ?string $categoryKey): int
    {
        // вставить или изменить категорию
        $insertID = wp_insert_category($args, true);
        if (isset($insertID->error_data['term_exists'])) {
            $insertID = $insertID->error_data['term_exists'];
        } else if (is_numeric($insertID)) {
            // при апдейте было бы и так совпадение, при вставке нужно добавить к категории наш id из xml
            if (is_null($categoryKey)) {
                throw new Exception('Нету $categoryKey');
            }
            add_term_meta($insertID, self::WP_CATEGORY_META_KEY_ID, $categoryKey, true);
        } else {
            throw new Exception('wp_insert_category response error');
        }
        return $insertID;
    }

}
