<?php

namespace App\Http\Controllers;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;


class BaseController extends Controller
{
    public function fetchDeployments() {
        $client = new Client();
        $response = $client->get('http://localhost:8081/apis/apps/v1/namespaces/default/deployments');
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
