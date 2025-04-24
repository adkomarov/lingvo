<?php

namespace Drupal\lingvo\Service;

use Ramsey\Collection\Sort;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Service;

class DataService
{
    // TODO: перенос функций из object.inc, кроме действий на странице

    protected $database;

    public function __construct(Connection $database)
    {
        $this->database = $database;
    }

    function _get_word_for_object($objID)
    {
        $database = Database::getConnection();
        $query = $database->select('lingvo_word', 'w')
            ->fields('w', [
                'id',
                'value',
                'icon',
                'uid',
                'pid',
                'status',
                'created',
                'uidmodified'
            ])
            ->fields('wr', ['rid', 'status' => 'rstat', 'uid' => 'ruid'])
            ->fields('r', ['index'])
            ->condition('w.objid', $objID)
            ->orderBy('w.value', 'ASC');

        $query->join('lingvo_wordinregion', 'wr', 'w.id = wr.wid');

        $query->join('lingvo_region', 'r', 'wr.rid = r.id');

        $result = $query->execute();

        $out = [];
        $rid = [];


        foreach ($result as $ar) {
            if (empty($ar->value)) {
                $ar->value = '- пусто -';
            }

            $word_id = $ar->id;

            if (!empty($ar->index)) {
                $rid[$word_id][] = [
                    'rstat' => $ar->rstat,
                    'index' => $ar->index,
                    'ruid' => $ar->ruid
                ];
            }

            if (!isset($out[$word_id])) {
                $out[$word_id] = [
                    'value' => $ar->value,
                    'icon' => $ar->icon,
                    'pid' => $ar->pid,
                    'uid' => $ar->uid,
                    'status' => $ar->status,
                    'created' => $ar->created,
                    'uidmod' => $ar->uidmodified
                ];
            }
        }
        foreach ($rid as $word_id => $regions) {
            $out[$word_id]['rid'] = $regions;
        }

        return $out;
    }

    function type_comment($type)
    {

        $in = array(
            'comment' => 0,
            'example' => 1,
            'selo' => 2
        );

        if (isset($in[$type])) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Получает список всех регионов.
     *
     * @return array
     *   Ассоциативный массив регионов, где ключ — ID, значение — название.
     */
    function all_region()
    {
        $array = [0 => '- не выбрано -'];

        $connection = Database::getConnection();
        $query = $connection->select('lingvo_region', 'lr')
            ->fields('lr', ['id', 'name'])
            ->condition('name', '', '!=')
            ->groupBy('name')
            ->orderBy('name', 'ASC');

        $result = $query->execute();

        foreach ($result as $record) {
            $array[$record->id] = $record->name;
        }

        return $array;
    }

    /**
     * Возвращает предыдущий вопрос по номеру.
     *
     * @param int $num
     *   Текущий номер вопроса.
     *
     * @return int
     *   Номер предыдущего вопроса или 0, если не найден.
     */
    function _last_quesion(int $num): int
    {
        $connection = Database::getConnection();

        $query = $connection->select('lingvo_object', 'lo')
            ->fields('lo', ['num'])
            ->condition('num', $num, '<')
            ->orderBy('num', 'DESC')
            ->range(0, 1);

        $result = $query->execute()->fetchField();

        return $result !== FALSE ? (int) $result : 0;
    }

    /**
     * Возвращает следующий вопрос.
     *
     * @param int $num
     *   Текущий номер вопроса.
     *
     * @return int
     *   Номер следующего вопроса или 0, если не найден.
     */
    function _next_quesion($num)
    {
        $connection = Database::getConnection();

        $query = $connection->select('lingvo_object', 'lo')
            ->fields('lo', ['num'])
            ->condition('num', $num, '>')
            ->orderBy('num', 'ASC')
            ->range(0, 1);

        $result = $query->execute()->fetchField();

        return $result !== FALSE ? (int) $result : 0;
    }


    // TODO: Дубликат в массиве '908'
    /**
     * Возвращает координаты региона по индексу.
     *
     * @param string $index
     *   Индекс региона.
     * @param bool $all
     *   Если TRUE, возвращаются все регионы.
     *
     * @return array|mixed|null
     *   Ассоциат. массив координат всех регионов.
     */
    function _position($index, $all = FALSE)
    {
        $region = array(
            '945' => array(0.6709, 0.5285),
            '965' => array(0.6599, 0.7188),
            '964' => array(0.5820, 0.7309),
            '971' => array(0.5597, 0.8150),
            '970' => array(0.4101, 0.9016),
            '977' => array(0.3503, 0.9754),
            '963' => array(0.4193, 0.7924),
            '962а' => array(0.1905, 0.8287),
            '962' => array(0.3041, 0.7001),
            '958' => array(0.4910, 0.6912),
            '953' => array(0.2934, 0.5893),
            '954' => array(0.4544, 0.5666),
            //'908' => array(0.1359, 0.1629),
            '896' => array(0.2530, 0.1304),
            '909' => array(0.3335, 0.1972),
            '897' => array(0.4347, 0.1732),
            '910' => array(0.5206, 0.1784),
            '898' => array(0.6118, 0.1441),
            '931' => array(0.5900, 0.3084),
            '937' => array(0.6898, 0.2776),
            '921' => array(0.8693, 0.2684),
            '938' => array(0.7570, 0.4001),
            '921a' => array(0.7853, 0.5249),
            '955' => array(0.5571, 0.5783),
            '949' => array(0.3001, 0.4851),
            '943' => array(0.4365, 0.4237),
            '944' => array(0.5446, 0.4337),
            '942' => array(0.2190, 0.4179),
            '936' => array(0.3424, 0.3616),
            '930' => array(0.4830, 0.2899),
            '920' => array(0.2476, 0.2418),
            '928' => array(0.1052, 0.2984),
            '908' => array(0.1270, 0.1749),
            '929' => array(0.1839, 0.3239),
            '921а' => array(0.8068, 0.5699),
        );

        if ($all)
            return $region;

        return $region[$index];
    }

    /**
     * Возвращает JSON-ответ с сообщением об ошибке и завершает выполнение.
     *
     * @param string $str
     *   Сообщение об ошибке.
     */
    function set_message(string $str = '')
    {
        $response = new JsonResponse([
            'status' => 'error',
            'msg' => !empty($str) ? $str : 'Сервер возвратил ошибку. Пожалуйста, проверьте запрос.',
        ]);
        $response->send();
        exit();
    }

    // TODO: наименование функции

    function _addClass($str, $class)
    {
        $cls = preg_split('/\s+/', trim($str));
        if (in_array($class, $cls)) {
            return $str;
        }
        $cls[] = $class;
        return implode(' ', $cls);
    }

    function _regionIndex($id) {
        $connection = \Drupal::database();
      
        $query = $connection->select('lingvo_region', 'r')
          ->fields('r', ['index'])
          ->condition('id', $id)
          ->range(0, 1);
      
        $result = $query->execute()->fetchField();
      
        return $result !== FALSE ? $result : NULL;
      }


    function validate_array($owner)
    {
        foreach ($owner as $key => $value) {
            if (!validate($value))
                return FALSE;
        }
    
        return TRUE;
    }

    function validate($get) {
        if ((!empty($get)) && (is_numeric($get))) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Получает путь к нужному файлу из директории icon с учётом количества файлов в каждой директории.
     *
     * @param int $arg
     *   Порядковый номер файла.
     * @param string $module
     *   Название модуля.
     *
     * @return string|null
     *   Путь к файлу или NULL, если файл не найден.
     */
    public function get_icon_file_path(int $arg, string $module = 'lingvo'): ?string
    {
        /** @var \Drupal\Core\Extension\ModuleHandlerInterface $module_handler */
        $module_handler = \Drupal::service('module_handler');
        $module_path = $module_handler->getModule($module)->getPath();
        $icon_path = DRUPAL_ROOT . '/' . $module_path . '/images/icon';
        //return $icon_path;
        if (!is_dir($icon_path)) {
            return 'icon path isnt dir';
        }

        $dirs = array_filter(glob($icon_path . '/*'), 'is_dir');
        $dir_count = count($dirs);
        if ($dir_count === 0) {
            return 'count dir of icon is null';
        }
        $dir_files_count = [];
        foreach ($dirs as $dir) {
            $files = glob($dir . '/*.png');
            $dir_files_count[] = count($files);
        }
        $file_index = null;
        $directory_index = null;
        $total_files = 0;
        foreach ($dir_files_count as $index => $count) {

            if ($arg <= ($total_files + $count)) {
                $directory_index = $index + 1; 
                $file_index = $arg - $total_files; 
                break;
            }
            $total_files += $count;
        }
        if ($file_index === null || $directory_index === null) {
            return NULL; 
        }
        $concrete_dir = $dirs[$directory_index];
        $concrete_files = array_filter(glob($concrete_dir . '/*'), 'is_file');
        $concrete_files_png = array_filter($concrete_files, function($file) {
            return str_ends_with($file, '.png');
        });
        sort($concrete_files_png);
        $concrete_file = $concrete_files_png[$file_index];
        
        return $concrete_file;
        //file_exists($full_path) ? $relative_path : NULL;
        //$relative_path = $module_path . "/icon/{$concrete_dir}/{$concrete_file}.png";
        //$full_path = DRUPAL_ROOT . '/' . $relative_path;
        //return $dirs;
        //$concrete_file = array_filter(glob($concrete_dir . '/*'), 'is_file')[$file_index];
        //return dump(count($dir_files_count)%$file_index);
    }

}