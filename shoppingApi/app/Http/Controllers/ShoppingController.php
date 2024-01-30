<?php

namespace App\Http\Controllers;

use App\Model\My;
use App\Model\Shopping;
use App\Total;
use Illuminate\Http\Request;

class ShoppingController extends Controller
{
    //
    public function getShop($page,Request $request)
    {
        $offset=($page-1)*10;
        $signature = $request->input('signature');
        $Userid = My::where('signature', '=', $signature)->first()["id"];
        $data = Shopping::with(['Good'])->where('isBuy', '=', 'false')->orderBy('shoppingid', 'desc')->offset($offset)->limit(10)->get()->Where('Userid', '=', $Userid);
        foreach ($data as $Key => $model) {
            $model["good"]["Goodimg"] = env('APP_URL') . substr_replace($model["good"]["Goodimg"], "", 0, 1);
        }
        $result['data']=$data;
        Total::json($result);
    }
    public function changeNum(Request $request)
    {
        $shoppingid = $request->input('id');
        $Num=$request->input('num');
        $data = Shopping::where('shoppingid', '=', $shoppingid)->update(
            [
                'Num' => $Num,
            ]
        );
        Total::json('success');
    }
    public function DeleteShop($shoppingid)
    {
        $data = Shopping::find($shoppingid);
        $data = $data->delete();
        Total::json('success');
    }
    public function changeType($shoppingid, $type, $color)
    {
        $data = Shopping::where('shoppingid', '=', $shoppingid)->update(
            [
                'type' => $type,
                'color' => $color,
            ]
        );
    }
    public function changeChecked(Request $request)
    {
        $shoppingid = $request->input('id');
        $isChecked = $request->input('checked');
        $data = Shopping::where('shoppingid', '=', $shoppingid)->update(
            [
                'isChecked' => $isChecked,
            ]
        );
        Total::json('success');
    }
    public function CheckedAll($signature, $isChecked)
    {
        $Userid = My::where('signature', '=', $signature)->first()["id"];
        $data = Shopping::where('Userid', '=', $Userid)->update(
            [
                'isChecked' => $isChecked,
            ]
        );
    }
    public function AddShop(Request $request)
    {
        $Goodid = $request->input('Goodid');
        $Userid = $request->input('Userid');
        $type = $request->input('type');
        $color = $request->input('color');
        $Num = $request->input('Num');
        $result = Shopping::where('Goodid', '=', $Goodid)->where('Userid', $Userid)->where('isBuy', '=', 'false')->first();
        if ($result) {
            Shopping::where('Goodid', '=', $Goodid)->where('Userid', $Userid)->update(
                [
                    'type' => $type,
                    'color' => $color,
                    'Num' => $result["Num"] + $Num,
                ]
            );
        } else {
            Shopping::insert([
                'Goodid' => $Goodid,
                'Userid' => $Userid,
                'type' => $type,
                'color' => $color,
                'Num' => $Num,
                'isChecked' => '0',
                'isBuy' => 'false',
            ]);
        }
    }
    public function deleteChecked(Request $request)
    {
        $signature = $request->input('signature');
        $Userid = My::where('signature', '=', $signature)->first()["id"];
        $data = Shopping::where('Userid', '=', $Userid)->where('isChecked', '=', '1')->delete();
    }
}
