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

        return $this->response->json($response)->withStatus($status);
    }

    public function deposit($request)
    {
        $account = AccountModel::find($request['destination']);

        if(empty($account)){
            AccountModel::create(['id'=>$request['destination'], 'amount'=> $request['amount']]);
        }else{
            $account->amount = $account->amount + $request['amount'];

            $account->save();
        }
        $status = 201;
        $response = ['destination' => 
                        [
                        "id" => $account->id,
                        "balance" => $account->amount
                        ]
                    ];

        return[$status, $response];
       }

    public function withdraw($request)
    {
        $account = AccountModel::find($request['origin']);

        if(!empty($account)){
            $account->amount -= $request['amount'];
            $account->save();

            $status = 201;
            $response = [
                            "origin" => [
                                "id" => $account->id,
                                "balance" =>$account->amount
                            ]
                        ];
            return [$status, $response];
        }

        $status = 400;
        $response = 0;

        return [$status, $response];

    }

    public function transfer($request)
    {

    }

    public function balance()
    {
        $request = $this->request->all();
       
        $account = AccountModel::find($request['account_id']);

        if(!empty($account)){            
            return $this->response->withContent($account->amount)->withStatus(200);
        }
        return $this->response->json(0)->withStatus(400);
    }
}