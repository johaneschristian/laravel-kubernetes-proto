<?php

namespace App\Http\Controllers;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;


class BaseController extends Controller
{
    public $token = 'eyJhbGciOiJSUzI1NiIsImtpZCI6InFQSndxQUZ3T2dsaDBlT0g1a1pZVlluQ0VKcExzd0dQOFA4U0dRUGJrcjgifQ.eyJhdWQiOlsiaHR0cHM6Ly9rdWJlcm5ldGVzLmRlZmF1bHQuc3ZjLmNsdXN0ZXIubG9jYWwiXSwiZXhwIjoxNjc5NTYyMDUwLCJpYXQiOjE2Nzk1NTg0NTAsImlzcyI6Imh0dHBzOi8va3ViZXJuZXRlcy5kZWZhdWx0LnN2Yy5jbHVzdGVyLmxvY2FsIiwia3ViZXJuZXRlcy5pbyI6eyJuYW1lc3BhY2UiOiJkZWZhdWx0Iiwic2VydmljZWFjY291bnQiOnsibmFtZSI6InBvc3RtYW4iLCJ1aWQiOiJhNTIxNzc5Zi0zNmU3LTQwMDgtYjU2NC05YjBmYWVmNjdjYmIifX0sIm5iZiI6MTY3OTU1ODQ1MCwic3ViIjoic3lzdGVtOnNlcnZpY2VhY2NvdW50OmRlZmF1bHQ6cG9zdG1hbiJ9.Av71BfoxlrwblYI77IY8eTke9t1350ZtmejU8Fx8T-a0UbaYKmz4-jWk438lgY5x6xq0-gtnn9H3L6Ffp6lCN8_jx161colefTQy890GUP07yQLzKVniiP9_h-_-CW_mBYiWdOz-FZeJ2nXMf7klfDvjMFST5DySEBu59YbH5D2PZ4_zD6mkTYRCR8OxLzpepSrC7btbDd0pC5VM59eRd5TQJbntW9CCNrfe_4uC0f3BcIppJBBIRMdFeotzWsVfPbhTHzEFaaswcpkJsv1Nnoj51b5ulqUcWXh0CPNfmzNAyF5lMZt8TFCwMf2tIok1oHnFsmE_tBlcpqjmWEzo4w';

    public function fetchDeployments() {
        $client = new Client(['verify' => false]);
        $response = $client->get(
            'https://192.168.59.110:8443/apis/apps/v1/namespaces/default/deployments',
            [
                'headers' => [
                    'authorization' => 'Bearer ' . $this->token
                ]
            ]
        );
        return json_decode($response->getBody(), true);
    }

    public function createDeployment() {
        $deployment_data = '
            apiVersion: apps/v1
            kind: Deployment
            metadata:
              name: deployment-through-api
            spec:
              replicas: 1
              selector:
                matchLabels:
                  app: deployment-through-api
              template:
                metadata:
                  labels:
                    app: deployment-through-api
                spec:
                  containers:
                  - name: deployment-through-api
                    image: johanestarigan/azure-laravel-proto
                    resources:
                      limits:
                        memory: "128Mi"
                        cpu: "100m"
                    ports:
                    - containerPort: 8000
        ';

        $client = new Client();
        $response = $client->post(
            'http://localhost:8081/apis/apps/v1/namespaces/default/deployments',
            [
                'headers' => [
                    'content-type' => 'application/yaml'
                ],
                'body' => $deployment_data
            ],
        );

        return json_decode($response->getBody(), true);
    }

    public function scaleDeployment($replicas) {
        $scale_data = json_encode([
            'spec' => [
                'replicas' => (int) $replicas
            ]
        ]);

        $client = new Client();
        try {
            $response = $client->patch(
                'http://localhost:8081/apis/apps/v1/namespaces/default/deployments/deployment-through-api',
                [
                    'headers' => [
                        'content-type' => 'application/strategic-merge-patch+json'
                    ],
                    'body' => $scale_data
                ]
            );
        } catch (Exception $e) {
            return $e->getMessage();
        }


        return json_decode($response->getBody(), true);
    }

    /**
     * @throws GuzzleException
     */
    public function createService() {
        $service_data = json_encode([
            'kind' => 'Service',
            'apiVersion' => 'v1',
            'metadata' => [
                'name' => 'hello-web-deployment-service'
            ],
            'spec' => [
                'ports' => [
                    [
                        'port' => 8000,
                        'targetPort' => 8000
                    ]
                ],
                'selector' => [
                    'app' => 'deployment-through-api'
                ],
                'type' => 'NodePort'
            ]
        ]);

        $request_data = [
            'headers' => [
                'content-type' => 'application/json'
            ],
            'body' => $service_data
        ];

        $client = new Client();
        $response = $client->post(
            'http://localhost:8081/api/v1/namespaces/default/services/',
            $request_data
        );

        return json_decode($response->getBody(), true);
    }
}
