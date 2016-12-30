<?php

class Tickets
{
    public $fromStation = null;
    public $toStation = null;
    public $date = null;

    const API = 'https://kyfw.12306.cn/otn/leftTicket/queryZ?purpose_codes=ADULT';
    const GET_STATION_API = 'https://kyfw.12306.cn/otn/resources/js/framework/station_name.js?station_version=1.8992';

    public function __construct($fromStation = null, $toStation = null, $date = null)
    {
        if (!file_exists(ROOT_PATH . '/data/station.json')) {
            $this->parseStation();
        }
        $this->fromStation = $fromStation;
        $this->toStation = $toStation;
        $this->date = $date;
    }

    /**
     * 入口函数
     */
    public function run()
    {
        if (is_null($this->fromStation) || is_null($this->toStation))
            throw new Exception('起始站不能为空!');
        is_null($this->date) && $date = date('Y-m-d');

        $url = self::API. '&leftTicketDTO.train_date='.$this->date.'&leftTicketDTO.from_station='.$this->fromStation .'&leftTicketDTO.to_station='.$this->toStation;
        $ticketInfo = $this->curlGet($url);
        return $ticketInfo;
    }

    /**
     * 解析火车站信息
     */
    private function parseStation()
    {
        $url = self::GET_STATION_API;
        $station = $this->curlGet($url, false);

        if (empty($station)) {
            throw new Exception('获取站点信息失败！');
        }

        $delStr = "var station_names ='"; //需要截断的字符
        $station = substr($station, strlen($delStr), strlen($station));

        $station = explode('@', $station);
        $json = [
            'message' => ''
        ];

        foreach ($station as $key => $vo) {
            if (empty($vo)) continue;

            $st = explode('|', $vo);
            $json['value'][] = [
                'stationName' => $st['1'],
                'shortName' => $st['3'],
                'stationFlag' => $st['2']
            ];
        }
        unset($station);

        file_put_contents(ROOT_PATH . '/data/station.json', json_encode($json));
    }

    /**
     * 采集数据
     * @param $url
     * @param $decode
     */
    private function curlGet($url, $decode = true)
    {
        $ch = curl_init();
        $timeout = 5;
        $header = [
            'Accept:*/*',
            'Accept-Charset:GBK,utf-8;q=0.7,*;q=0.3',
            'Accept-Encoding:gzip,deflate,sdch',
            'Accept-Language:zh-CN,zh;q=0.8,ja;q=0.6,en;q=0.4',
            'Connection:keep-alive',
            'Host:kyfw.12306.cn',
            'Referer:https://kyfw.12306.cn/otn/lcxxcx/init',
        ];
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip"); //指定gzip压缩
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $result = curl_exec($ch);
        curl_close($ch);
        $decode && $result = json_decode($result, true);
        return $result;
    }

}