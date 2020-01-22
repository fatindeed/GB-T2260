<?php

$urls = [
    // 2019年11月中华人民共和国县以上行政区划代码
    'GB-T2260' => 'http://www.mca.gov.cn/article/sj/xzqh/2019/2019/201912251506.html',
    // 2013年中华人民共和国县以下行政区划代码
    'GB-T10114-2013' => 'http://files2.mca.gov.cn/cws/201404/20140404125738290.htm',
    // 2014年中华人民共和国县以下行政区划变更情况
    'GB-T10114-2014' => 'http://www.mca.gov.cn/article/sj/tjbz/a/2014/201706020952.html',
    // 2015年中华人民共和国县以下行政区划变更情况
    'GB-T10114-2015' => 'http://www.mca.gov.cn/article/sj/tjbz/a/2015/below/201602/20160200880232.htm',
    // 2016年中华人民共和国县以下行政区划变更情况
    'GB-T10114-2016' => 'http://www.mca.gov.cn/article/sj/xzqh/1980/201705/201705051130.html',
    // 2017年中华人民共和国县以下行政区划变更情况
    'GB-T10114-2017' => 'http://www.mca.gov.cn/article/sj/xzqh/1980/201803/201803131629.html',
    // 2018年中华人民共和国县以下行政区划变更情况
    'GB-T10114-2018' => 'http://www.mca.gov.cn/article/sj/xzqh/2018/201903/201903011016.html',
];

foreach ($urls as $key => $url) {
    if ($key == 'GB-T2260' || $key == 'GB-T10114-2013') {
        continue;
    }
    var_dump($key);
    $content = file_get_contents($url);
    if (! preg_match_all('|<tr.*>(.*)</tr>|isU', strip_tags($content, '<tr><td>'), $trs)) {
        throw new Exception('Tr Not Found', 1);
    }
    $colnum = 0;
    $return = '';
    $rowspan = [];
    foreach ($trs[1] as $tr) {
        if (! preg_match_all('|<td(.*)>(.*)</td>|isU', trim($tr), $tds)) {
            throw new Exception('Td Not Found', 1);
        }
        if ($colnum > 0) {
            foreach ($tds[1] as $i => $tdAttr) {
                if (preg_match('|colspan="?(\d+)"?|i', $tdAttr, $span)) {
                    array_splice($tds[1], $i, 0, array_pad([], $span[1] - 1, $tds[1][$i]));
                    array_splice($tds[2], $i, 0, array_pad([], $span[1] - 1, $tds[2][$i]));
                }
            }
            if (count($rowspan) > 0) {
                foreach ($rowspan as $i => $values) {
                    array_splice($tds[1], $i, 0, '');
                    array_splice($tds[2], $i, 0, array_shift($rowspan[$i]));
                    if (count($rowspan[$i]) == 0) {
                        unset($rowspan[$i]);
                    }
                }
            }
            foreach ($tds[1] as $i => $tdAttr) {
                if (preg_match('|rowspan="?(\d+)"?|i', $tdAttr, $span)) {
                    $rowspan[$i] = array_pad([], $span[1] - 1, $tds[2][$i]);
                }
            }
            ksort($rowspan);
            $row = array_map('ztrim', $tds[2]);
            if (count($row) != $colnum) {
                var_dump($tds);
                exit;
            }
            if (mb_substr($row[0], 0, 2) == '注：') {
                break;
            }
            for ($i = $colnum; $i > 6; $i--) { 
                array_shift($row);
            }
            if ($row[0]) {
                if (! is_numeric($row[0])) {
                    print_r($row);
                    throw new UnexpectedValueException(json_encode($row));
                }
                if ((empty($row[3]) || $row[0] == $row[3]) && $row[4]) {
                    $return .= "R\t$row[0]\t" . html_entity_decode($row[4]) . "\n";
                } else {
                    $return .= "D\t$row[0]\t" . html_entity_decode($row[1]) . "\n";
                }
            }
            if ($row[3] && is_numeric($row[3]) && $row[4]) {
                $return .= "A\t$row[3]\t" . html_entity_decode($row[4]) . "\n";
            }
        } elseif ($tds[2][0] == '序号' || $tds[2][0] == '&#24207;&#21495;') {
            $colnum = count($tds[2]);
            var_dump($colnum);
        }
    }
    // TODO: UNIQUE
    file_put_contents($key . '.txt', $return);
}

function ztrim($value)
{
    return trim(str_replace(['&nbsp;', '　'], [' ', ' '], html_entity_decode($value)));
}
