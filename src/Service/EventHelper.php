<?php

namespace App\Service;

use App\Entity\Event;

/**
 * Class EventHelper
 * @package App\Service
 *
 * This class formats the event data to ease template implementation.
 */
Class EventHelper
{
    /**
     * @var int event id
     */
    public $id;

    /**
     * @var string event name
     */
    public $name;

    /** @var string event description */
    public $description;

    /**
     * @var int event total expense
     */
    public $grandTotal;

    /**
     * @var array operations list order by date and user pseudo
     */
    public $operations;

    /**
     * @var array event total summary per user
     */
    public $summary;

    /**
     * constructor
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->id = $event->getId();
        $this->name = $event->getName();
        $this->description = $event->getDescription();

        $this->operations = array();
        $this->summary = array();

        foreach ($event->getOperations() as $index => $operation) {

            $this->operations[$index] = [
                'id' => $operation->getId(),
                'date' => $operation->getDate(),
                'description' => $operation->getDescription(),
                'pseudo' => $operation->getUser()->getUserEvents()[0]->getPseudo(),
                'totalExpense' => 0,
                'totalPayment' => 0
            ];

            foreach ($operation->getExpenses() as $expense) {
                $this->operations[$index]['totalExpense'] += $expense->getExpense();
                $this->operations[$index]['totalPayment'] += $expense->getPayment();
                $this->operations[$index]['details'][$expense->getUser()->getId()] = [
                    'pseudo' => $expense->getUser()->getUserEvents()[0]->getPseudo(),
                    'expense' => $expense->getExpense(),
                    'payment' => $expense->getPayment()
                ];
            }

            $this->grandTotal += $this->operations[$index]['totalExpense'];

            foreach ($this->operations[$index]['details'] as $userId => $detail) {
                $amountToPay = $this->operations[$index]['totalExpense'] / $this->operations[$index]['totalPayment'] * $detail['payment'];
                $this->operations[$index]['details'][$userId] += [
                    'amountToPay' => $amountToPay,
                    'balance' => $detail['expense'] - $amountToPay
                ];
            }
        }

        foreach ($this->operations as $operation) {
            foreach ($operation['details'] as $userId => $detail) {
                if (!isset($this->summary[$userId])) {
                    $this->summary[$userId] = [
                        'pseudo' => $detail['pseudo'],
                        'expense' => 0,
                        'amountToPay' => 0,
                        'balance' => 0
                    ];
                }
                $this->summary[$userId]['expense'] += $detail['expense'];
                $this->summary[$userId]['amountToPay'] += $detail['amountToPay'];
                $this->summary[$userId]['balance'] += $detail['balance'];
            }
        }
    }
}
