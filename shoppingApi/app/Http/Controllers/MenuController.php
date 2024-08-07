<?php

namespace App\Http\Controllers;

use App\Model\Menu;
use App\Total;
use DOMDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    //验证是不是svg标签
    public function isValidSvg($value)
    {
        $dom = new DOMDocument();
        // 禁用错误报告，避免 DOM 解析错误输出到屏幕
        libxml_use_internal_errors(true);
        // 添加一个根元素，将 SVG 字符串包装为一个完整的 XML 文档
        $svgString = $value;
        // 加载 SVG 字符串到 DOM 文档
        $loaded = $dom->loadXML($svgString);
        // 启用错误报告
        libxml_use_internal_errors(false);
        // 验证 DOM 解析是否成功，且根元素是否为 <svg>
        return $loaded && $dom->documentElement->tagName === 'svg';
    }
    //验证规则
    public function validateData($request, $id)
    {
        $validator = Validator::make($request->all(), [
            'level' => 'required|numeric|in:0,1',
            'text' => 'required_if:level,0|max:10',
            'icon' => [
                'required_if:level,0',
                function ($attribute, $value, $fail) use ($request) {
                    if (!$request->input('level')) {
                        if (!$this->isValidSvg($value)) {
                            $fail('不是svg图像');
                        }
                    }
                },
            ],
            'path' => ['required_unless:level,0', function ($attribute, $value, $fail) use ($request) {
                if ($request->input('level')) {
                    if (!preg_match('/\/+/', $value)) {
                        $fail('路径不符合要求');
                    }
                }
            }],
            'parentId' => ['required_unless:level,0', function ($attribute, $value, $fail) use ($request, $id) {
                if ($request->input('level')) {
                    if (!is_numeric($value)) {
                        $fail('parentId 必须是数字');
                    } elseif ($id == $value) {
                        $fail('父亲ID不能和自身相同');
                    }
                }
            }],
        ]);
        return $validator;
    }
    //更新
    public function Update($id, Request $request)
    {
        $validator = $this->validateData($request, $id);
        if ($validator->fails()) {
            Total::json('校验失败', -1);
        }
        $text = $request->input('text');
        $icon = $request->input('icon');
        $level = $request->input('level');
        $path = $request->input('path');
        $parentId = $request->input('parentId');
        //分层级再进行验证
        if ($level === 0) {
            $parentId = 0;
        } elseif ($parentId === 0) {
            Total::json('上级菜单错误', -1);
        } else {
            $hasChildren = Menu::where('parentId', '=', $id)->count();
            if ($hasChildren > 0) {
                Total::json('父节点有子节点', -1);
            }

            $hasparentId = Menu::where('id', '=', $parentId)->where('level', '=', '0')->count();
            if ($hasparentId === 0) {
                Total::json('上级菜单不存在', -1);
            }
        }

        $data = [
            'text' => $text,
            'icon' => $icon,
            'path' => $path,
            'level' => $level,
            'parentId' => $parentId,
        ];
        $result = Menu::where('id', $id)->update($data);
        if ($result) {
            Total::json('更新成功');
        } else {
            Total::json('更新失败', -1);
        }
    }
    //增加
    public function Insert(Request $request)
    {
        $validator = $this->validateData($request, null);
        if ($validator->fails()) {
            Total::json('校验失败', -1);
        }
        $text = $request->input('text');
        $icon = $request->input('icon');
        $path = $request->input('path');
        $level = $request->input('level');
        $parentId = $request->input('parentId');

        //分层级再进行验证
        if ($level === 0) {
            $parentId = 0;
            $path = null;
        } elseif ($parentId === 0) {
            Total::json('上级菜单错误', -1);
        } else {
            $icon = null;
        }
        $data = [
            'text' => $text,
            'icon' => $icon,
            'path' => $path,
            'level' => $level,
            'parentId' => $parentId,
        ];
        $result = Menu::insert($data);
        if ($result) {
            Total::json('增加成功');
        } else {
            Total::json('增加失败', -1);
        }
    }
    //查找
    public function likeSelect(Request $request)
    {
        $menu = Menu::where('level', '=', '1')->get();
        foreach ($menu as $key => $children) {
            $parentId = $children["id"];
            $children["children"] = Menu::where('parentId', '=', $parentId)->get();
            foreach ($children["children"] as $key2 => $grandson) {
                $parentId = $grandson["id"];
                $grandson["children"] = Menu::where('parentId', '=', $parentId)->get();
            }
        }
        $data["data"] = $menu;
        Total::json($data);
    }
    //删除
    public function Delete($id)
    {
        $sonMenu=Menu::where('parentId', $id)->first();
        if ($sonMenu) {
            Total::json('无法删除该菜单，因为它具有下级菜单。', -1);
        }
        $deletedRows  = Menu::where('id', $id)->delete();
        if ($deletedRows) {
            Total::json('删除成功');
        } else {
            Total::json('删除失败', -1);
        }
    }
}
