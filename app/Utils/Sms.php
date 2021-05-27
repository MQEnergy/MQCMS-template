<?php

declare(strict_types=1);

namespace App\Utils;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

class Sms
{
    public static $instance;

    public $regionId;

    /**
     * @param mixed $instance
     */
    public static function getInstance(): Sms
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param string $regionId
     * @return $this
     * @throws ClientException
     */
    public function initDefaultClient($regionId = 'cn-hangzhou')
    {
        $this->regionId = $regionId;
        AlibabaCloud::accessKeyClient(env('OSS_ACCESS_ID'), env('OSS_ACCESS_SECRET'))->regionId($regionId)->asDefaultClient();
        return $this;
    }

    /**
     * 发送短信验证码
     * @param $phone
     * @param $tempCode
     * @param string $signName
     * @param string $regionId
     */
    public function sendCodeSms($phone, $tempCode, $code, $signName = 'Ucarter集市')
    {
        try {
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId' => $this->regionId,
                        'PhoneNumbers' => $phone,
                        'SignName' => $signName,
                        'TemplateCode' => 'SMS_' . $tempCode,
                        'TemplateParam' => json_encode([
                            'code' => $code
                        ])
                    ]
                ])
                ->request();
            return $result->toArray();

        } catch (ClientException $e) {
            return $e->getErrorMessage();

        } catch (ServerException $e) {
            return $e->getErrorMessage();
        }
    }
}