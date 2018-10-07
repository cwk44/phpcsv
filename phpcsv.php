<?php
/**
 * csv工具类
 * Class Lib_csv
 */
class Lib_Csv {

    /**
     * 读取csv函数
     * @param string $filename 文件路径
     * @param array $header 头部的行数列表（如传入array(0,1,2)，代表前三行是头部，会把前三行抽到header里返回）
     * @return array|bool
     */
    function readCsv($filename, $header = array()) {
        if (!$filename) {
            return false;
        }

        setlocale(LC_ALL, 'zh_CN.GBK');

        $handle = fopen($filename, 'r');
        $code = mb_detect_encoding(file_get_contents($filename), 'gbk, UTF-8');

        //读取文件
        $file_data = array();
        while ($data = fgetcsv($handle, 1000)) {
            if ($code != 'UTF-8') {
                foreach ($data as $k => $v) {
                    $data[$k] = mb_convert_encoding($v, "UTF-8", "GBK");
                }
            }

            $file_data[] = $data;
        }

        fclose($handle);

        $result = array();

        //抽取头部
        if ($header) {
            foreach ($header as $key => $value) {
                $result['header'][] = $file_data[$value];
                unset($file_data[$value]);
            }
        }

        $result['data'] = $file_data;

        return $result;
    }

    //在页面上导出一个csv
    public function exportCsv($filename, $data = array(), $charset = 'gbk') {
        $content = $this->getCsvData($data, $charset == 'gbk', $filename);
        $this->exportFile($filename, $content, $charset);
    }

    //将一个数据转成csv格式的数据
    public function getCsvData($data, $is_to_gbk = false, $filename = '') {
        if (!$filename) {
            $filename = uniqid().'.csv';
        }
        $this->exportCsvFile($filename, $data, $is_to_gbk);
        $file_path = TEMP_DIR . '/' . $filename;
        $data = file_get_contents($file_path);

        unlink($file_path);

        return $data;
    }

    public function exportFile($filename, $content = '', $encode = 'utf-8') {  //gbk
        header('Content-type: application/octet-stream;charset='.$encode);
        header("Content-Disposition: attachment; filename=$filename");

        echo $content;
    }

    //导出一个csv文件到某个路径
    public function exportCsvFile($filename, $data = array(), $is_to_gbk = true, $dir_name = TEMP_DIR) {
        $file_path = $dir_name . '/' . $filename;

        if (!is_dir($dir_name)) {
            mkdir($dir_name);
        }

        $fp = fopen($file_path, 'w');

        foreach ($data as $line) {
            $line = is_array($line) ? $line : array($line);

            if ($is_to_gbk) {
                foreach ($line as $key => $value) {
                    $line[$key] = mb_convert_encoding($value, 'gbk', 'utf8');
                }
            }

            fputcsv($fp, $line);
        }

        fclose($fp);

        return true;
    }

    //导出一个csv文件到某个路径,根据header输出
    public function exportCsvFileWithHeader($filename, $data, $header, $is_to_gbk = true, $dir_name = TEMP_DIR) {
        $csv_data = array();

        //头部
        $csv_data[] = array_values($header);

        //数据
        foreach ($data as $_data) {
            $line_data = array();
            foreach ($header as $key => $key_name) {
                $line_data[] = $_data[$key] ? $_data[$key] : "";
            }
            $csv_data[] = $line_data;
        }
        unset($data);

        return $this->exportCsvFile($filename, $csv_data, $is_to_gbk, $dir_name);
    }
}
