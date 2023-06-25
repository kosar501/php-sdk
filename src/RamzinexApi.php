<?php

namespace ramzinex;


use Psr\SimpleCache\InvalidArgumentException;

class RamzinexApi
{

    /**
     * @var string
     */
    private $headers;

    private string $secret;
    private string $api_key;

    private FileCache $cache_file;

    public function __construct($secret = null, $api_key = null, array $headers = null, $cache_folder = null)
    {
        if (!extension_loaded('curl')) {
            die('cURL library is not loaded');
            exit;
        }
        $this->secret = $secret;
        $this->api_key = $api_key;
        $this->headers = $headers;
        $this->cache_file = new FileCache($cache_folder);
    }

    /**
     * دریافت مشخصات وضعیت بازارهای رمزینکس *
     * @return mixed
     */
    public function getAllPrice()
    {
        return $this->execute('https://publicapi.ramzinex.com/exchange/api/v1.0/exchange/pairs');
    }

    /**
     * لیست سفارشات بازار*
     * @param $pairId
     * @return mixed
     */
    public function getOrderBook($pairId)
    {
        return $this->execute('https://publicapi.ramzinex.com/exchange/api/v1.0/exchange/orderbooks/' . $pairId . '/buys_sells');
    }

    /**
     * مشخصات یک بازار مشخص *
     * @param $pairId
     * @return mixed
     */
    public function getPrice($pairId)
    {
        return $this->execute('https://publicapi.ramzinex.com/exchange/api/v1.0/exchange/pairs/' . $pairId);
    }

    /**
     * دریافت سفارش‌های کاربر *
     * @param array|null $body |limit,offset,pairs,states,isbuy|
     * @return mixed
     */
    public function getOrders(array $body = null)
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/orders3', true, true, $body);
    }

    /**
     * مشخصات یک سفارش مشخص *
     * @param $orderId
     * @return mixed
     */
    public function getOneOrder($orderId)
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/orders2/' . $orderId, false, true);
    }

    /**
     * ارسال سفارش محدود *
     * @param $pairId
     * @param $amount
     * @param $price
     * @param $type |buy,sell|
     * @return mixed
     */
    public function setLimitOrder($pairId, $amount, $price, $type)
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/orders/limit/', true, true, ['pair_id' => $pairId, 'amount' => $amount, 'price' => $price, 'type' => $type]);
    }

    /**
     * ارسال سفارش بازار *
     * @param $pairId
     * @param $amount
     * @param $type |buy,sell|
     * @return mixed
     */
    public function setMarketOrder($pairId, $amount, $type)
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/orders/market/', true, true, ['pair_id' => $pairId, 'amount' => $amount, 'type' => $type]);
    }

    /**
     * کنسل کردن سفارش موجود *
     * @param $orderId
     * @return mixed
     */
    public function cancelOrder($orderId)
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/orders/' . $orderId . '/cancel', true, true);
    }

    /**
     * میزان دارایی کاربر *
     * @return mixed
     */
    public function getBalanceSummary()
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/funds/summaryDesktop', false, true);
    }

    /**
     * میزان سرمایه در دسترس کاربر برای یک ارز مشخص *
     * @param $currencyID
     * @return false|mixed
     */
    public function getAllOneBalance($currencyID)
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/funds/total/currency/' . $currencyID, false, true);
    }


    /**
     * @return mixed
     */
    public function setRefreshBalance()
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/funds/refresh', true, true);
    }

    /**
     * @param $currencyId
     * @return mixed
     */
    public function getCurrencyBalance($currencyId)
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/funds/available/currency/' . $currencyId, false, true);
    }


    /**
     *  میزان سرمایه در دسترس کاربر برای یک ارز مشخص *
     * @param $currencyId
     * @return mixed
     */
    public function getTotalCurrencyBalance($currencyId)
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/funds/total/currency/' . $currencyId, false, true);
    }

    /**
     *  میزان سرمایه در حال معمامله کاربر برای یک ارز مشخص *
     * @param $currencyId
     * @return mixed
     */
    public function getInOrdersCurrencyBalance($currencyId)
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/funds/in_orders/currency/' . $currencyId, false, true);
    }

    /**
     * مشخصات سرمایه کاربر برای همه ارزها*
     * @return mixed
     */
    public function getUserFunds()
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/funds/details', false, true);
    }

    /**
     * شبکه های موجود برای واریز و برداشت یک ارز *
     * @param array|null $data |currency_id,withdraw,deposit|
     * @return mixed
     */
    public function getNetworks(array $data = null)
    {
        $fields_string = "";
        if (!is_null($data)) {
            $fields_string = http_build_query($data);
        }
        return $this->execute('https://publicapi.ramzinex.com/exchange/api/v1.0/exchange/networks?' . $fields_string, false, false);
    }

    /**
     * آدرس های موجود برای یک کاربر *
     * @param array $networks
     * @return mixed
     */
    public function getAddresses(array $networks)
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/addresses', true, true, ['networks' => $networks]);
    }

    /**
     * مشخصات واریز های انجام شده کاربر برای یک ارز مشخص *
     * @param $currencyId
     * @param array|null $data |limit,offset|
     * @return mixed
     */
    public function getCurrencyDeposits($currencyId, array $data = null)
    {
        $fields_string = "";
        if (!is_null($data)) {
            $fields_string = http_build_query($data);
        }
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/funds/deposits/currency/' . $currencyId . '?' . $fields_string, false, true);
    }

    /**
     *  مشخصات یک واریز مشخص *
     * @param $depositId
     * @return mixed
     */
    public function getDepositDetail($depositId)
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/funds/deposits/' . $depositId, false, true);
    }

    /**
     * مشخصات برداشت‌ها *
     * @param array|null $data |limit,offset|
     * @return mixed
     */
    public function getCurrencyWithdraws(array $data = null)
    {
        $fields_string = "";
        if (!is_null($data)) {
            $fields_string = http_build_query($data);
        }
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/funds/withdraws' . '?' . $fields_string, false, true);
    }

    /**
     * مشخصات یک برداشت خاص *
     * @param $withdrawId
     * @return mixed
     */
    public function getWithdrawDetail($withdrawId)
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/funds/withdraws/' . $withdrawId, false, true);
    }

    /**
     * مشخصات یک ارز *
     * @param $currencyId
     * @return array
     */
    public function getCurrency($currencyId = null)
    {
        return $this->execute('https://publicapi.ramzinex.com/exchange/api/v1.0/exchange/currencies/' . $currencyId, false, true);

    }

    /**
     * لیست ارزها *
     * @param $currencyId
     * @return array
     */
    public function getCurrencies(): array
    {
        return $this->execute('https://publicapi.ramzinex.com/exchange/api/v1.0/exchange/currencies', false, true);

    }


    /**
     * ایجاد توکن خصوصی با استفاده از api_key && secret_key *
     * مدت زمان اعتبار 10 دقیقه می باشد *
     * @return mixed
     * @throws InvalidArgumentException
     */
    private function generateToken()
    {
        $data = $this->parseData($this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/auth/api_key/getToken', true, false, [
            'secret' => $this->secret,
            'api_key' => $this->api_key
        ]));

        //save in cache file //
        $this->cache_file->setItem('ramzinex_token', $this->token, 600);

        return @$data['token'];
    }

    /**
     * ایجاد مجدد توکن در صورت انقضا *
     * @return mixed
     * @throws InvalidArgumentException
     */
    private function refreshToken()
    {
        if ($token = $this->cache_file->getItem('ramzinex_token')) {
            return $token;
        } else
            return $this->generateToken();

    }


    /**
     * درصورتی که api نیاز به ارسال توکن دارد مقدار private را true ارسال کنید *
     * @param $url
     * @param $post
     * @param $private
     * @param $data
     * @return array
     * @throws InvalidArgumentException
     */
    protected function execute($url, $post = false, $private = false, $data = null): array
    {


        $headers = array(
            'Accept: application/json',
            'charset: utf-8',
            'Content-Type: application/json'
        );
        if ($private) {
            $headers[] = 'Authorization: Bearer ' . $this->refreshToken();
        }
        if ($this->headers != null) {
            foreach ($this->headers as $header) {
                $headers[] = $header;
            }
        }


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        } else {
            curl_setopt($ch, CURLOPT_POST, false);
        }
        $result = curl_exec($ch);
        curl_close($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return [
            "http_code" => $httpCode,
            "result" => json_decode($result, true)
        ];


    }


    private function parseData($response)
    {
        if ($response['http_code'] == 200)
            return @$response['result'] && @$response['result']['data'] ? $response['result']['data'] : [];
    }

}

