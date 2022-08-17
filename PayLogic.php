<?php

class PayLogic
{
    const IOS_FORMAL = "https://buy.itunes.apple.com/verifyReceipt";
    const IOS_SANDBOX = "https://sandbox.itunes.apple.com/verifyReceipt";
  
    //iap验证
    public static function checkReceipt($receipt, $isSandbox = true)
    {
        $info = self::getReceiptData($receipt, $isSandbox);
        if (empty($info)) {
            throw new \Exception('empty response from ios', -1);
        }
        if ($info['status'] == 0) {
            return ['isSandbox' => 0];
        } else {
            if ($info->status == 21007) {
                $info = self::getReceiptData($receipt, true);
                if ($info['status'] == 0) {
                    return ['isSandbox' => 1];
                } else {
                    throw new \Exception("error status is " . $info['status'], -1);
                }
            } else {
                throw new \Exception("error status is " . $info['status'], -1);
            }

        }

    }

    //获取receiptdata数据
    private static function getReceiptData($receipt, $isSandbox = false)
    {
        $endpoint = $isSandbox ? self::IOS_SANDBOX : self::IOS_FORMAL;

        $postData = json_encode(['receipt-data' => $receipt]);
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);  //这两行一定要加，不加会报SSL 错误
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $errmsg = curl_error($ch);
        curl_close($ch);

        return json_decode($response, true);

        return $response;
    }
}
