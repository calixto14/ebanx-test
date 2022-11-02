<?php
namespace App\Controller;

use App\Model\AccountModel;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

class AccountController extends AbstractController
{
    public function eventBalancer()
    {
        $request = $this->request->all();

        $method = $request['type'];

        list($status, $response) = $this->$method($request);

        return $this->response->withContent($response)->withStatus($status);
    }

    public function deposit($request)
    {
        $account = AccountModel::find($request['destination']);

        if(empty($account)){
            $account = new  AccountModel;  

            $account->id = $request['destination'];

            $account->amount = $request['amount'];

            if($account->save()){
                $account->id = $request['destination'];
            }
        }else{
            $account->amount = $account->amount + $request['amount'];

            $account->save();
        }

        $status = 201;
        $response = "{\"destination\": {\"id\":\"{$account->id}\", \"balance\":{$account->amount}}}";
        

        return[$status, $response];
       }

    public function withdraw($request)
    {
        $account = AccountModel::find($request['origin']);

        if(!empty($account)){
            $account->amount -= $request['amount'];
            $account->save();

            $status = 201;
            $response = "{\"origin\": {\"id\":\"{$account->id}\", \"balance\":{$account->amount}}}";
            
            return [$status, $response];
        }

        $status = 404;
        $response = "0";

        return [$status, $response];
    }

    public function transfer($request)
    {
        $accountOrigem = AccountModel::find($request['origin']);

        $status = 404;

        $response = "0";

        if(empty($accountOrigem) || !$this->checkAmount($accountOrigem, $request['amount'])){            

            return [$status, $response];
        }        

        if(!$this->decremetAmountOfOrigin($accountOrigem, $request['amount'])){
            return [$status, $response];
        }

       

        $status = 201;

        $response = "{\"origin\": {\"id\":\"{$accountOrigem->id}\", \"balance\":{$accountOrigem->amount}}, \"destination\": {\"id\": \"{$request['destination']}\", \"balance\":{$request['amount']}}}";
        
        return [$status, $response];
    }

    public function balance()
    {
        $request = $this->request->all();
       
        $account = AccountModel::find($request['account_id']);

        if(!empty($account)){            
            return $this->response->json((int)$account->amount)->withStatus(200);
        }
        return $this->response->json(0)->withStatus(404);
    }

    public function reset()
    {
        AccountModel::truncate();
        return $this->response->withContent('OK');
    }

    protected function checkAmount(&$accountOrigem, $transferAmount)
    {       
        if($accountOrigem->amount < $transferAmount){
            return false;
        }

        return true;
    }

    protected function decremetAmountOfOrigin(&$account, $amount)
    {
        $account->amount -= $amount;

        if($account->save()){
            return true;
        }
        
    }

    protected function incrementAmountOfDestination(&$account, $amount)
    {
        $account->amount += $amount;

        if($account->save()){
            return true;
        }
    }
}