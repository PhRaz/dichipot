<?php

use App\Entity\Event;


Class Operation {

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
            foreach ($operation->getExpenses() as $expense) {
                $pseudo = $expense->getUser()->getUserEvents()[0]->getPseudo();
                $totalExpense += $expense->getExpense();
                $balance[$id][$expense->getUser()->getName()]['expense'] = $expense->getExpense();
                $balance[$id][$expense->getUser()->getName()]['pseudo'] = $pseudo;
            }

            $totalPayment = 0;
            foreach ($operation->getPayments() as $payment) {
                $totalPayment += $payment->getAmount();
                $balance[$id][$payment->getUser()->getName()]['payment'] = $payment->getAmount();
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