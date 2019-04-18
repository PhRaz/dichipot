<?php

namespace App\Service;

use App\Entity\Event;


Class Helper {

    /**
     * Compute event account balance.
     * @param Event $event
     * @return array
     */
    public function getBalance(Event $event): array
    {
        $balance = array();
        foreach ($event->getOperations() as $operation) {

            $operationId = $operation->getId();

            $totalExpense = 0;
            $totalPayment = 0;
            foreach ($operation->getExpenses() as $expense) {
                $totalExpense += $expense->getExpense();
                $totalPayment += $expense->getPayment();
                $userId = $expense->getUser()->getId();
                $balance[$operationId][$userId]['expense'] = $expense->getExpense();
                $balance[$operationId][$userId]['payment'] = $expense->getPayment();
                $balance[$operationId][$userId]['pseudo'] = $expense->getUser()->getUserEvents()[0]->getPseudo();
            }

            foreach ($balance[$operationId] as $userId => $data) {
                $amountToPay = $totalExpense / $totalPayment * $balance[$operationId][$userId]['payment'];
                $balance[$operationId][$userId] = array_merge($balance[$operationId][$userId], [
                    'amountToPay' => $amountToPay,
                    'balance' => $balance[$operationId][$userId]['expense'] - $amountToPay
                ]);
            }
        }

        $total = array();
        foreach ($balance as $operationId => $balanceData) {
            foreach ($balanceData as $userId => $userData) {
                if (!isset($total[$userId])) {
                    $total[$userId] = [
                        'expense' => 0,
                        'payment' => 0,
                        'amountToPay' => 0,
                        'balance' => 0,
                        'pseudo' => $userData['pseudo']
                    ];
                }
                $total[$userId]['expense'] += $userData['expense'];
                $total[$userId]['payment'] += $userData['payment'];
                $total[$userId]['amountToPay'] += $userData['amountToPay'];
                $total[$userId]['balance'] += $userData['balance'];
            }
        }
        return array($balance, $total);
    }
}