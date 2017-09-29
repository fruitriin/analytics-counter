<?php

namespace AnalyticsCounter;

const analytics_uri = 'https://www.googleapis.com/analytics/v3';
const scope_analytics_readonly = 'https://www.googleapis.com/auth/analytics.readonly';
const credential_filename = 'analyticsCredentials.json';
const expression_string = 'ga:sessions';
const alias_string = 'sessions';
const analytics_launch_date = '2005-01-01';
const default_credentialPath = __DIR__ . ' ../credential.json';

use google\appengine\api;

class Counter
{
    private $reports;

    /**
     * Counter constructor.
     * @param string $credentialPath
     */
    public function __construct($credentialPath = default_credentialPath)
    {

        $this->validation($credentialPath);
        $analyticsReportingRespoce = $this->initializeAnalytics($credentialPath);
        $this->getReport($analyticsReportingRespoce);
    }

    /**
     * @param null $credentialPath
     * @return int
     */
    private function validation($credentialPath = null)
    {
        $response = ["code" => 400, "error" => "request paramater invalid."];
        if (isset($credentialPath) && !is_string($credentialPath)) {
            $response["messages"][] = "credential path is must be string";
        }

        if (!isset($_POST["id"])) {
            $response["messages"][] = "Analytics View Id is need";
        }

        if (isset($_POST["days"])
            && (isset($_POST["start_day"]) || isset($_POST["end_day"]))
        ) {
            $response["messages"][]
                = "days or start_day and (or) end_day cant set same time";
        }

        if (isset($_POST["days"])) {
            if (!is_array($_POST["days"])) {
                $response["messages"][] = "days must be array";
            } else {
                foreach ($_POST['days'] as $dayPair) {
                    if (!key_exists('start', $dayPair)
                        || !key_exists('end', $dayPair)
                    ) {
                        $response["messages"][]
                            = "days array need start and end params pair";
                    }
                }
            }
        }

        if (isset($_POST["prefix_name"]) && is_string($_POST["prefix_name"])) {
            $response["messages"][] = "prefix_name shuld be string";
        }

        if (!isset($response["messages"])) {
            return 0;
        }
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    private function validationEnvs($clientId, $clientEmail, $signedKey)
    {

        if (empty($clientId) || empty($clientEmail) || empty($signedKey)) {
            $response = [
                "code" => 401,
                "error" => "authenticate from credential fail and env vars both failed."
            ];

            if (empty($clientId)) {
                $response["messages"][]
                    = "Env var: AC_CLIENT_ID  is need for auth";
            }

            if (empty($clientEmail)) {
                $response["messages"][]
                    = "Env var: AC_CLIENT_EMAIL  is need for auth";
            }
            if (empty($signedKey)) {
                $response["messages"][]
                    = "Env var: AC_SIGHNED_KEY  is need for auth";
            }
            if (isset($response['messages']) && count($response["messages"]) > 0) {
                echo json_encode($response, JSON_PRETTY_PRINT);
                exit;
            }

        }

    }

    /**
     * @param $credentialPath
     * @return \Google_Service_AnalyticsReporting
     */
    private function initializeAnalytics($credentialPath)
    {
        $client = new \Google_Client();
        if (file_exists($credentialPath)) {
            //設定ファイルから取得(json)
            //バリデーションはsetAuthCOnfigがやってくれる
            $client->setAuthConfig($credentialPath);
        } else {
            //環境変数から取得

            $client->useApplicationDefaultCredentials();
            $clientId = getenv('AC_CLIENT_ID');
            $clientEmail = getenv("AC_CLIENT_EMAIL");
            $signedKey = getenv("AC_SIGHNED_KEY");

            //環境変数のバリデーション
            $this->validationEnvs($clientId, $clientEmail, $signedKey);

            //credentialClientのセット
            $client->setClientId($clientId);
            $client->setConfig("client_email", $clientEmail);
            $client->setConfig("signing_key", $signedKey);
            $client->setConfig("signing_algorithm", "HS256");
        }
        $client->addScope(\Google_Service_Analytics::ANALYTICS_READONLY);

        //Prefix Nameを付与する
        $prefix = key_exists("prefiix_name", $_POST) ?? $_POST["prefix_name"] . ' ';
        $client->setApplicationName($prefix . "Analytics Counter");

        return new \Google_Service_AnalyticsReporting($client);
    }

    /**
     * @param \Google_Service_AnalyticsReporting $analyticsReporting
     */
    public function getReport(\Google_Service_AnalyticsReporting $analyticsReporting)
    {
        $reports = null;

        if (isset($_POST['days'])) {
            foreach ($_POST['days'] as $daysPair) {
                $reports[] = $this->getReportCore($analyticsReporting, $daysPair['start'], $daysPair['end']);
            }
        } else {
            $startDay = key_exists('start_day', $_POST) ? $_POST["start_day"]
                : date('Y-m-d', strtotime(analytics_launch_date));
            $endDay = key_exists('end_day', $_POST) ? $_POST["end_day"] : 'today';
            $reports = $this->getReportCore($analyticsReporting, $startDay, $endDay);
        }
        $this->reports = $reports;
    }

    /**
     * @param \Google_Service_AnalyticsReporting $analytics
     * @param $start
     * @param $end
     * @return \Google_Service_AnalyticsReporting_Report
     */
    private function getReportCore(\Google_Service_AnalyticsReporting $analytics, $start, $end)
    {
        $VIEW_ID = $_POST["id"];

        // Create the DateRange object.
        $dateRange = new \Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate($start);
        $dateRange->setEndDate($end);

        // Create the Metrics object.
        $sessions = new \Google_Service_AnalyticsReporting_Metric();
        $sessions->setExpression(expression_string);
        $sessions->setAlias(alias_string);

        // Create the ReportRequest object.
        $request = new \Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($VIEW_ID);
        $request->setDateRanges($dateRange);
        $request->setMetrics(array($sessions));

        $body = new \Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests(array($request));
        return $analytics->reports->batchGet($body)->getReports();

    }


    /**
     * @return array|float|int
     */
    function printResults()
    {
        $reports = $this->reports;

        $response = [];
        foreach ($reports as $report) {
            if (is_array($report)) {
                foreach ($report as $r) {
                    $response[] = $this->printResultCore($r);
                }
            } else {
                $response = $this->printResultCore($report);
            }
        }
        return $response;
    }

    /**
     * @param \Google_Service_AnalyticsReporting_Report $report
     * @return array|float|int
     */
    private function printResultCore(
        \Google_Service_AnalyticsReporting_Report $report
    )
    {

        $header = $report->columnHeader;
        $metricHeaders = $header->metricHeader->getMetricHeaderEntries();
        $rows = $report->data->rows;
        $responce = null;
        foreach ($rows as $k => $row) {
            $responce = $this->getMetricsData($row, $metricHeaders);
        }
        return $responce;
    }

    /**
     * @param $row
     * @param $headers
     * @return array|float|int
     */
    private function getMetricsData($row, $headers)
    {
        $response = [];
        $metrics = $row->metrics;
        if (count($metrics) > 1) {
            foreach ($metrics as $key => $metric) {
                $metric_value = array_sum($metric->values);
                $response[$headers[$key]->name] = $metric_value;
            }
        } else {
            if (count($metrics) == 1) {
                $metric_value = array_sum($metrics[0]->values);
                $response = $metric_value;
            }
        }
        return $response;
    }
}
