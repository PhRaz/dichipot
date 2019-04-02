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

            $id = $operation->getId();

            $totalExpense = 0;
            $totalPayment = 0;
            foreach ($operation->getExpenses() as $expense) {
                $totalExpense += $expense->getExpense();
                $totalPayment += $expense->getPayment();
                $userName = $expense->getUser()->getName();
                $balance[$id][$userName]['expense'] = $expense->getExpense();
                $balance[$id][$userName]['payment'] = $expense->getPayment();
                $balance[$id][$userName]['pseudo'] = $expense->getUser()->getUserEvents()[0]->getPseudo();
            }

            foreach ($balance[$id] as $userName => $data) {
                $amountToPay = $totalExpense / $totalPayment * $balance[$id][$userName]['payment'];
                $balance[$id][$userName] = array_merge($balance[$id][$userName], [
                    'amountToPay' => $amountToPay,
                    'balance' => $balance[$id][$userName]['expense'] - $amountToPay
                ]);
            }
        }

        $total = array();
        foreach ($balance as $id => $balanceData) {
            foreach ($balanceData as $userName => $userData) {
                if (!isset($total[$userName])) {
                    $total[$userName] = [
                        'expense' => 0,
                        'payment' => 0,
                        'amountToPay' => 0,
                        'balance' => 0
                    ];
                }
                $total[$userName]['expense'] += $userData['expense'];
                $total[$userName]['payment'] += $userData['payment'];
                $total[$userName]['amountToPay'] += $userData['amountToPay'];
                $total[$userName]['balance'] += $userData['balance'];
            }
        }
        return array($balance, $total);
    }
}