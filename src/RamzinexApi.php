<?php

namespace ramzinex;


use Psr\SimpleCache\InvalidArgumentException;

class RamzinexApi
{

    /**
     * @var string
     */
    private string|array|null $headers;

    private string $secret;
    private string $api_key;

    private FileCache $cache;

    public function __construct($secret = null, $api_key = null, array $headers = null)
    {
        if (!extension_loaded('curl')) {
            die('cURL library is not loaded');
            exit;
        }
        $this->secret = $secret;
        $this->api_key = $api_key;
        $this->headers = $headers;
        $this->cache = new FileCache();
    }

    /**
     * دریافت مشخصات وضعیت بازارهای رمزینکس *
     * @return array
     * @throws InvalidArgumentException
     */
    public function getAllPrice(): array
    {
        return $this->execute('https://publicapi.ramzinex.com/exchange/api/v1.0/exchange/pairs');
    }

    /**
     * لیست سفارشات بازار*
     * @param $pairId
     * @return array
     * @throws InvalidArgumentException
     */
    public function getOrderBook($pairId): array
    {
        return $this->execute('https://publicapi.ramzinex.com/exchange/api/v1.0/exchange/orderbooks/' . $pairId . '/buys_sells');
    }

    /**
     * مشخصات یک بازار مشخص *
     * @param $pairId
     * @return array
     * @throws InvalidArgumentException
     */
    public function getPrice($pairId): array
    {
        return $this->execute('https://publicapi.ramzinex.com/exchange/api/v1.0/exchange/pairs/' . $pairId);
    }

    /**
     * دریافت سفارش‌های کاربر *
     * @param array|null $body |limit,offset,pairs,states,isbuy|
     * @return array
     * @throws InvalidArgumentException
     */
    public function getOrders(array $body = null): array
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/orders3', true, true, $body);
    }

    /**
     * مشخصات یک سفارش مشخص *
     * @param $orderId
     * @return array
     * @throws InvalidArgumentException
     */
    public function getOneOrder($orderId): array
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/orders2/' . $orderId, false, true);
    }

    /**
     * ارسال سفارش محدود *
     * @param $pairId
     * @param $amount
     * @param $price
     * @param $type |buy,sell|
     * @return array
     * @throws InvalidArgumentException
     */
    public function setLimitOrder($pairId, $amount, $price, $type): array
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/orders/limit/', true, true, ['pair_id' => $pairId, 'amount' => $amount, 'price' => $price, 'type' => $type]);
    }

    /**
     * ارسال سفارش بازار *
     * @param $pairId
     * @param $amount
     * @param $type |buy,sell|
     * @return array
     * @throws InvalidArgumentException
     */
    public function setMarketOrder($pairId, $amount, $type): array
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/orders/market/', true, true, ['pair_id' => $pairId, 'amount' => $amount, 'type' => $type]);
    }

    /**
     * کنسل کردن سفارش موجود *
     * @param $orderId
     * @return array
     * @throws InvalidArgumentException
     */
    public function cancelOrder($orderId): array
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/orders/' . $orderId . '/cancel', true, true);
    }

    /**
     * میزان دارایی کاربر *
     * @return array
     * @throws InvalidArgumentException
     */
    public function getBalanceSummary(): array
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/funds/summaryDesktop', false, true);
    }

    /**
     * میزان سرمایه در دسترس کاربر برای یک ارز مشخص *
     * @param $currencyID
     * @return array
     * @throws InvalidArgumentException
     */
    public function getAllOneBalance($currencyID): array
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/funds/total/currency/' . $currencyID, false, true);
    }


    /**
     * @return array
     * @throws InvalidArgumentException
     */
    public function setRefreshBalance(): array
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/funds/refresh', true, true);
    }

    /**
     * @param $currencyId
     * @return array
     * @throws InvalidArgumentException
     */
    public function getCurrencyBalance($currencyId): array
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/funds/available/currency/' . $currencyId, false, true);
    }


    /**
     *  میزان سرمایه در دسترس کاربر برای یک ارز مشخص *
     * @param $currencyId
     * @return array
     * @throws InvalidArgumentException
     */
    public function getTotalCurrencyBalance($currencyId): array
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/funds/total/currency/' . $currencyId, false, true);
    }

    /**
     *  میزان سرمایه در حال معمامله کاربر برای یک ارز مشخص *
     * @param $currencyId
     * @return array
     * @throws InvalidArgumentException
     */
    public function getInOrdersCurrencyBalance($currencyId): array
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/funds/in_orders/currency/' . $currencyId, false, true);
    }

    /**
     * مشخصات سرمایه کاربر برای همه ارزها*
     * @return array
     * @throws InvalidArgumentException
     */
    public function getUserFunds(): array
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/funds/details', false, true);
    }

    /**
     * شبکه های موجود برای واریز و برداشت یک ارز *
     * @param array|null $data |currency_id,withdraw,deposit|
     * @return array
     * @throws InvalidArgumentException
     */
    public function getNetworks(array $data = null): array
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
     * @return array
     * @throws InvalidArgumentException
     */
    public function getAddresses(array $networks): array
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/addresses', true, true, ['networks' => $networks]);
    }

    /**
     * مشخصات واریز های انجام شده کاربر برای یک ارز مشخص *
     * @param $currencyId
     * @param array|null $data |limit,offset|
     * @return array
     * @throws InvalidArgumentException
     */
    public function getCurrencyDeposits($currencyId, array $data = null): array
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
     * @return array
     * @throws InvalidArgumentException
     */
    public function getDepositDetail($depositId): array
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/funds/deposits/' . $depositId, false, true);
    }

    /**
     * مشخصات برداشت‌ها *
     * @param array|null $data |limit,offset|
     * @return array
     * @throws InvalidArgumentException
     */
    public function getCurrencyWithdraws(array $data = null): array
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
     * @return array
     * @throws InvalidArgumentException
     */
    public function getWithdrawDetail($withdrawId): array
    {
        return $this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/users/me/funds/withdraws/' . $withdrawId, false, true);
    }

    /**
     * مشخصات یک ارز *
     * @param $currencyId
     * @return array
     * @throws InvalidArgumentException
     */
    public function getCurrency($currencyId = null): array
    {
        return $this->execute('https://publicapi.ramzinex.com/exchange/api/v1.0/exchange/currencies/' . $currencyId, false, true);

    }

    /**
     * لیست ارزها *
     * @param $currencyId
     * @return array
     * @throws InvalidArgumentException
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
    private function generateToken(): mixed
    {
        $data = $this->parseData($this->execute('https://ramzinex.com/exchange/api/v1.0/exchange/auth/api_key/getToken', true, false, [
            'secret' => $this->secret,
            'api_key' => $this->api_key
        ]));

        //save in cache file //
        $this->cache->setItem('ramzinex_token', @$data['token'], 600);

        return @$data['token'];
    }

    /**
     * ایجاد مجدد توکن در صورت انقضا *
     * @return mixed
     * @throws InvalidArgumentException
     */
    private function refreshToken(): mixed
    {
        if (!$this->cache->isExpired('ramzinex_token')) {
            return $this->cache->getItem('ramzinex_token');
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
            $headers[] = 'Authorization2: Bearer ' . $this->refreshToken();
            $headers[] = 'x-api-key:' . $this->api_key;
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

