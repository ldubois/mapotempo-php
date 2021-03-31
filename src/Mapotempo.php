<?php

namespace Ldubois\Mapotempo;

use Assert\Assertion;
use Buzz;
use Buzz\Client\AbstractClient;
use Buzz\Message\Response;
use Buzz\Listener\CallbackListener;
use Buzz\Message\RequestInterface;
use phpDocumentor\Reflection\Types\Boolean;

class Mapotempo
{
    /** @var Buzz\Browser */
    protected $browser;

    /** @var string */
    private $url;
    /** @var string */
    private $apiKey;

    /** @var string */
    private $version = "0.1";

    /** @var string */
    private $table;

    public function __construct(string $url, string $apiKey, int $timeout = 10)
    {
        // @see https://github.com/kriswallsmith/Buzz/pull/186
        $listener = new CallbackListener(function (RequestInterface $request, $response = null) {
        });

        $this->browser = new Buzz\Browser(new Buzz\Client\Curl());
        $this->browser->addListener($listener);


        /** @var AbstractClient */
        $client = $this->browser->getClient();
        $client->setTimeout($timeout);

        $this->url = $url;
        $this->apiKey = $apiKey;
    }

    public function createTableManipulator(string $table): TableManipulator
    {
        return new TableManipulator($this, $table);
    }

    public function createRecord(string $table, array $fields): void
    {

        /** @var Response $response */
        $response = $this->browser->post(
            $this->getEndpoint($table),
            [
                'content-type' => 'application/json',
            ],
            json_encode(
                $fields
            )
        );

        $this->guardResponse($table, $response);
    }

    /**
     * This will update all fields of a table record, issuing a PUT request to the record endpoint. Any fields that are not included will be cleared ().
     *
     * @throws \Assert\AssertionFailedException
     */
    public function setRecord(string $table, array $criteria, array $fields): void
    {
        $record = $this->findRecord($table, $criteria);

        Assertion::notNull($record, 'Record not found');

        /** @var Response $response */
        $response = $this->browser->put(
            $this->getEndpoint($table, $record->getId()),
            [
                'content-type' => 'application/json',
            ],
            json_encode([
                'fields' => $fields,
            ])
        );

        $this->guardResponse($table, $response);
    }

    /**
     * This will update some (but not all) fields of a table record, issuing a PATCH request to the record endpoint. Any fields that are not included will not be updated.
     *
     * @throws \Assert\AssertionFailedException
     */
    public function updateRecord(string $table, array $criteria, array $fields): void
    {
        $record = $this->findRecord($table, $criteria);

        Assertion::notNull($record, 'Record not found');


        $this->updateRecordById($table, $record->getId(), $fields);
    }

    /**
     * This will update some (but not all) fields of a table record,
     *  issuing a PATCH request to the record endpoint.
     *  Any fields that are not included will not be updated.
     *
     * @throws \Assert\AssertionFailedException
     */
    public function updateRecordById(string $table, string $id, array $fields): void
    {

        /** @var Response $response */
        $response = $this->browser->put(
            $this->getEndpoint($table, $id),
            [
                'content-type' => 'application/json',
            ],
            json_encode([
                'fields' => $fields,
            ])
        );

        $this->guardResponse($table, $response);
    }

    public function containsRecord(string $table, array $criteria): bool
    {
        return null !== $this->findRecord($table, $criteria);
    }

    public function flushRecords(string $table): void
    {
        $records = $this->findRecords($table);

        /** @var Record $record */
        foreach ($records as $record) {
            /** @var Response $response */
            $response = $this->browser->delete(
                $this->getEndpoint($table, $record->getId()),
                [
                    'content-type' => 'application/json',
                ]
            );

            $this->guardResponse($table, $response);
        }
    }

    public function deleteRecord(string $table, array $criteria): void
    {
        $record = $this->findRecord($table, $criteria);

        Assertion::notNull($record, 'Record not found');

        /** @var Response $response */
        $response = $this->browser->delete(
            $this->getEndpoint($table, $record->getId()),
            [
                'content-type' => 'application/json',
            ]
        );

        $this->guardResponse($table, $response);
    }

    public function deleteRecords(string $table, array $criteria): void
    {
        $records = $this->findRecords($table, $criteria);
        foreach ($records as $record) {
            Assertion::notNull($record, 'Record not found');

            /** @var Response $response */
            $response = $this->browser->delete(
                $this->getEndpoint($table, $record->getId()),
                [
                    'content-type' => 'application/json',
                ]
            );

            $this->guardResponse($table, $response);
        }
    }

    public function getRecord(string $table, string $id): Record
    {
        $url = $this->getEndpoint($this->table, $id);

        /** @var Response $response */
        $response = $this->browser->get(
            $url,
            [
                'content-type' => 'application/json',
            ]
        );

        $data = json_decode($response->getContent(), true);

        if (empty($data['id'])) {
            throw new \RuntimeException(sprintf("No records have been found from '%s:%s'.", $table, $id));
        }

        return new Record($data['id'], $data);
    }

    public function findRecord(string $table, array $criteria): ?Record
    {

        $records = $this->findRecords($table, $criteria);

        if (count($records) > 1) {
            throw new \RuntimeException(sprintf("More than one records have been found from '%s:%s'.", $this->base, $table));
        }

        if (0 === count($records)) {
            return null;
        }

        return current($records);
    }

    protected function format($s)
    {
        if (is_array($s)) {
            $res = [];
            foreach ($s as $key => $value) {
                $res[$this->format($key)] = $this->format($value);
            }

            return $res;
        }

        $s = str_replace(' ', '%20', $s);

        return $s;
    }

    /**
     * Retrieve records
     *
     * @return Record[]
     */
    public function findRecords(string $table, array $ids = [], bool $withExtraProperties = false): array
    {
        $url = $this->getEndpoint($table);

        if (count($ids) > 0) {
            $formulas = [];
            foreach ($ids as $field => $value) {
                $field = $this->format($field);
                $formulas[] = sprintf("%s:%s", $field, $value);
            }

            $url .= trim(sprintf(
                '&ids=%s',
                implode(',', $formulas)
            ));
        }


        if ($withExtraProperties) {
            $url .= "&with_extra_properties=true";
        }
        /** @var Response $response */
        $response = $this->browser->get(
            $url,
            [
                'content-type' => 'application/json',
            ]
        );
        $data = json_decode($response->getContent(), true);

        if (empty($data)) {
            return [];
        }

        $result = array_map(function (array $value) {
            return new Record($value['id'], $value);
        }, $data);



        return  $result;
    }

    protected function getEndpoint(string $table, ?string $id = null): string
    {
        if ($id) {
            $urlPattern = $this->url . '/api/' . $this->version . '/%TABLE%/%ID%?api_key=%APIKEY%';

            return strtr($urlPattern, [
                '%APIKEY%' => $this->apiKey,
                '%TABLE%' => rawurlencode($table),
                '%ID%' => $id,
            ]);
        }

        $urlPattern = $this->url . '/api/' . $this->version . '/%TABLE%?api_key=%APIKEY%';

        return strtr($urlPattern, [
            '%APIKEY%' => $this->apiKey,
            '%TABLE%' => rawurlencode($table),
        ]);
    }

    protected function guardResponse(string $table, Response $response): void
    {
        if (429 === $response->getStatusCode()) {
            throw new \RuntimeException(sprintf('Rate limit reach on "%s:%s".', $this->base, $table));
        }

        switch ($response->getStatusCode()) {
            case 200:
            case 201://création réussi
                break;
            default:
                $content = json_decode($response->getContent(), true);

                if (isset($content['message'])) {
                    $message = $content['message'] ?? 'No details';
                } else {
                    $message = $content['error'][0] ?? 'No details';
                }

                throw new \RuntimeException(sprintf('An "%s" error occurred when trying to create record on "%s" : %s', $response->getStatusCode(), $table, $message));
        }
        if (200 !== $response->getStatusCode()) {
        }
    }
}
