<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\{Transaction, CommissionWithdrawalHistory, SalesOrder,Setting,User};
use Revolution\Google\Sheets\Facades\Sheets;
use Google;
use Google_Service_Sheets_ValueRange, Google_Service_Sheets;

class AddDataToSheet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:data-to-sheet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add sales order, transactions and commission_withdrawal_histories data to sheet';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /*Sales order data send start*/
        $sheetId = Setting::first()->google_sheet_id ?? '';
        $sheetName = 'ДДС месяц';
        $sheetRow = Sheets::spreadsheet($sheetId)
                        ->sheet($sheetName)
                        ->get();
        $sheetRowCount = count($sheetRow);
        $startRow = $startrange = $sheetRowCount + 1;
        $params = [];
        $saleids = [];
        $salesorderrecords = SalesOrder::where('is_sheet_added',0)->where('status',10)->get();
        if(!empty($salesorderrecords)) {
            echo "Sales Orders Records.\n";
            foreach($salesorderrecords as $saleorders) {
                echo $saleorders->order_no."\n";
                $driverInfo = isset($saleorders->assigneddriver->user) ? $saleorders->assigneddriver->user : null;
                if($driverInfo != null && !empty($driverInfo)) {
                    $newTotal = $saleorders->sold_amount + $saleorders->driver_amount;
                    $params[] = [
                            date("d/m/Y", strtotime($saleorders->closed_win_date)),
                            $newTotal,
                            "{$driverInfo->name} ({$driverInfo->city_id})",
                            '',
                            date('d.m.Y H:i:s', strtotime($saleorders->closed_win_date)) . "        " . (isset($saleorders->seller->country_dial_code) ? "+{$saleorders->seller->country_dial_code} {$saleorders->seller->phone}" : "") . "        " . $saleorders->customer_postal_code,
                            '',
                            'Продажи'
                    ];
                    $startRow++;
                    $params[] = [
                                date('d/m/Y', strtotime($saleorders->closed_win_date)),
                                -$saleorders->driver_amount,
                                "{$driverInfo->name} ({$driverInfo->city_id})",
                                '',
                                date('d.m.Y H:i:s', strtotime($saleorders->closed_win_date)) . "        " . (isset($saleorders->seller->country_dial_code) ? "+{$saleorders->seller->country_dial_code} {$saleorders->seller->phone}" : "") . "        " . $saleorders->customer_postal_code,
                                '',
                                'Зарплата производственного персонала'
                            ];
                    $startRow++;
                    $saleids[] = $saleorders->id;
                }
            }
            if(!empty($params)) {
                $range = "{$sheetName}!C{$startrange}:M{$startrange}";
                Sheets::spreadsheet($sheetId)->sheet($sheetName)->range($range)->append($params);
                Salesorder::whereIn('id',$saleids)->update(['is_sheet_added'=>1]);
            }

        }
        /*Sales order data send end*/

        /*Transaction records data send start*/
        $transactionrecords = Transaction::with('user')->where('is_sheet_added',0)->where('is_approved' , 1)->where('user_id', '!=', 1)->get();
        $transcationrowstart = $startRow;
        $transactionparams = $transactionids =[];
        if(!empty($transactionrecords)) {
           echo "Transaction Records.\n";

            foreach($transactionrecords as $transaction){
                $transactionids[] = $transaction->id;
                echo $transaction->transaction_id."\n";
                // $transactionrange = "{$sheetName}!C{$startRow}:M{$startRow}";
                $transactionparams[] = [
                        date('d/m/Y'),
                        -$transaction->amount,
                        ($transaction->user->name ?? '') . (isset($transaction->user->city_id) ? " ({$transaction->user->city_id})" : ''),
                        '',
                        '',
                        '',
                        'Выбытие — Перевод между счетами'
                ];
                $startRow++;
                $transactionparams[] = [
                    date('d/m/Y'),
                    $transaction->amount,
                    User::select('name')->where('id', 1)->first()->name ?? '',
                    '',
                    '',
                    '',
                    'Поступление — Перевод между счетами'
                ];
                $startRow++;
            }
            if(!empty($transactionparams)) {
                $trancationrange = "{$sheetName}!C{$transcationrowstart}:M{$transcationrowstart}";
                Sheets::spreadsheet($sheetId)->sheet($sheetName)->range($trancationrange)->append($transactionparams);
                Transaction::whereIn('id',$transactionids)->update(['is_sheet_added'=>1]);
            }
        }
        /*Transaction records data send end*/

        /*Commission withdrawal history records data send start*/
        $commissionwithdrawalhistory = CommissionWithdrawalHistory::with(['user', 'bank'])->where('is_sheet_added', 0)->get();
        $commisionparams = $commissionids =[];
        $commissionrowstart = $startRow;
        if(!empty($commissionwithdrawalhistory)){
            echo "Commission without history.\n";
            foreach($commissionwithdrawalhistory as $commisionhistory) {
                $commissionids[] = $commisionhistory->id;
                $orderDetails = json_decode($commisionhistory->orders, true);
                $ordersDetail = '';

                if ($orderDetails != null) {
                    $ordersDetail = implode("   ", SalesOrder::select('order_no')->whereIn('id', $orderDetails)->pluck('order_no')->toArray());
                }
                echo $ordersDetail."\n";
                // $commisionrange = "{$sheetName}!C{$startRow}:M{$startRow}";
                $commisionparams[] =
                    [
                        date('d/m/Y'),
                        -$commisionhistory->amount,
                        User::select('name')->where('id', 1)->first()->name ?? '',
                        '',
                        $ordersDetail,
                        '',
                        'Зарплата коммерческого персонала - Посредники'
                    ];

                $startRow++;
            }
            if(!empty($commisionparams)) {
                $commissionrangerange = "{$sheetName}!C{$commissionrowstart}:M{$commissionrowstart}";
                Sheets::spreadsheet($sheetId)->sheet($sheetName)->range($commissionrangerange)->append($commisionparams);
                CommissionWithdrawalHistory::whereIn('id',$commissionids)->update(['is_sheet_added'=>1]);
            }
        }
        /*Commission withdrawal history records data send end*/

    }
}
