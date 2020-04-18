<?php

namespace Rir\Neuroscan\Console;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;

class NeuroscanParseCommand extends Command
{
    protected $client;

    protected $login;
    protected $password;
    protected $loginUrl;
    protected $exportUrl;

    protected $targetClass;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'neuroscan:parse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parsing Neuroscan XML report data.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->client = new Client();

        $this->login     = config('neuroscan.login');
        $this->password  = config('neuroscan.password');
        $this->loginUrl  = config('neuroscan.login_url');
        $this->exportUrl = config('neuroscan.export_url');

        $this->targetClass = config('neuroscan.target_class');

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $sessionId = $this->getSessionID();
        if ($sessionId === null) return;

        $dateFrom = date('Y-m-d 00:00:00');
        $dateTo   = date('Y-m-d 23:59:59');

        $offset = 0;
        do {
            $exportData = $this->getExportData($sessionId, $offset, $dateFrom, $dateTo);
            $exportData = new \SimpleXMLElement($exportData);

            $cameraIds[] = array_map("strval", $exportData->xpath("/faces/face/CameraID"));
            $cameraIdsMerged = array_merge([], ...$cameraIds);

            $offset += 100;
        } while ($exportData->count() >= 100);

        $visitorsCounts = array_count_values($cameraIdsMerged);
        $placesCameras = $this->getPlacesCamerasIds();

        $placesCameraArr = array_intersect_key($placesCameras, $visitorsCounts);
        $visitorsCountsArr = array_intersect_key($visitorsCounts, $placesCameras);

        $visitorsPlaces = array_combine($visitorsCountsArr, $placesCameraArr);

        $placesVisitors = [];
        array_walk_recursive($visitorsPlaces, function($value, $key) use (&$placesVisitors) {
            if (!isset($placesVisitors[$value])) {
                $placesVisitors[$value] = $key;
            } else {
                $placesVisitors[$value] += $key;
            }
        });

        $model = new $this->targetClass();
        foreach ($placesVisitors as $placeId => $visitors) {
            $model::whereId($placeId)->update(['visitors' => $visitors]);
        }

        $this->info('Completed.');
    }

    private function getSessionID()
    {
        try {
            $response = $this->client->post(
                $this->loginUrl, [
                'form_params' => [
                    'Login'    => $this->login,
                    'Password' => $this->password
                ]
            ])
                ->getBody()
                ->getContents();
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $exception = (string)$e->getResponse()->getBody();
                return json_decode($exception);
            } else {
                return $e->getMessage();
            }
        }

        return optional(json_decode($response))->SessionID;
    }

    private function getExportData($sessionId, $offset, $dateFrom, $dateTo)
    {
        try {
            $response = $this->client->post(
                $this->exportUrl, [
                'form_params' => [
                    'SessionID' => $sessionId,
                    'Analytics' => 'FaceRecognitionWithDemographics',
                    'From'      => $dateFrom,
                    'To'        => $dateTo,
                    'Limit'     => '100',
                    'Offset'    => $offset
                ]
            ])
                ->getBody()
                ->getContents();
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $exception = (string)$e->getResponse()->getBody();
                return json_decode($exception);
            } else {
                return $e->getMessage();
            }
        }

        return $response;
    }

    /** Some hardcode here ;] */
    private function getPlacesCamerasIds()
    {
        return [
            1105 => 26,
            1108 => 71,
            1109 => 24,
            1111 => 25,
            1112 => 26,
            1113 => 27,
            1114 => 27,
        ];
    }
}
