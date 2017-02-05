<?php

if (! function_exists('str_putcsv')) {
    function str_putcsv($input, $delimiter = ',', $enclosure = '"')
    {
        $fp = fopen('php://temp', 'r+b');

        fputcsv($fp, $input, $delimiter, $enclosure);

        rewind($fp);
        $data = rtrim(stream_get_contents($fp), "\n");
        fclose($fp);

        return $data;
    }
}
