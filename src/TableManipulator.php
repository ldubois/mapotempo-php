<?php

namespace Ldubois\Mapotempo;

class TableManipulator
{
    private $client;
    private $table;

    public function __construct(Mapotempo $client, string $table)
    {
        $this->client = $client;
        $this->table = $table;
    }


    public function createRecord(array $fields): void
    {
        $this->client->createRecord($this->table, $fields);
    }

    public function setRecord(array $criteria, array $fields): void
    {
        $this->client->setRecord($this->table, $criteria, $fields);
    }

    public function updateRecord(array $criteria, array $fields): void
    {
        $this->client->updateRecord($this->table, $criteria, $fields);
    }

    public function updateRecords( array $data): void
    {
        $this->client->updateRecords($this->table, $data);
    }

    public function updateRecordById(string $id,array $fields): void
    {
        $this->client->updateRecordById($this->table, $id, $fields);
    }

    public function containsRecord(array $criteria = []): bool
    {
        return $this->client->containsRecord($this->table, $criteria);
    }

    public function flushRecords(): void
    {
        $this->client->flushRecords($this->table);
    }

    public function deleteRecord(array $criteria = []): void
    {
        $this->client->deleteRecord($this->table, $criteria);
    }

    public function getRecord(string $id): Record
    {
        return $this->client->getRecord($this->table, $id);
    }

    public function findRecord(array $criteria = []): ?Record
    {
        return $this->client->findRecord($this->table, $criteria);
    }

    public function findRecords(array $criteria = []): array
    {
        return $this->client->findRecords($this->table, $criteria);
    }
}