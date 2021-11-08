<?php

namespace App\Admin\Controllers\Rent;

use App\Models\RevenueExpenses;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;

class RevenueExpensesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '收入支出表';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RevenueExpenses());
        $user_obj = Auth::guard('admin')->user();

        $grid->header(function ($query) {
            $user_obj = Auth::guard('admin')->user();

            return '用户ID：'.$user_obj->id;
        });
        $house_data = DB::table('house')->get()->toarray();
        $house_list = [];
        foreach ($house_data as $key=>$house){
            $house_list[$house->id] = $house->community.$house->household.$house->room_number;
        }
        $re_list = DB::table('revenue_expenses')->get();
        $project_list = DB::table('project')->where('status','=','1')->get();
        $project_name_arr = [];
        $handler_arr = [];
        foreach ($project_list as $single_project) {
            $project_name_arr[$single_project->name] = $single_project->name;
        }
        foreach ($re_list as $single_re) {
            $handler_arr[$single_re->handler] = $single_re->handler;
        }

        $grid->column('id', __('编号'));
        $grid->column('type', __('类型'))->display(function ($type){
            if ($type == '1'){
                return '收入';
            }elseif ($type == '2'){
                return '支出';
            }
        });
        $grid->column('date', __('日期'));
        $grid->column('project_name', __('项目名'));
        $grid->column('des_detail', __('明细'))->editable();
        $grid->column('house_id', __('房间现名'))->display(function ($house_id) use($house_list){
            if ($house_id == '0'){
                return '';
            }else if(array_key_exists($house_id,$house_list)){
                return $house_list[$house_id];
            }else{
                return '未知';
            }

        });
        $grid->column('house_name', __('房间快照名'));
        $grid->column('amount', __('金额'))->display(function ($amount){
            return '￥'.$amount;
        });
        $grid->column('handler', __('经手人'));
        $grid->column('bill', __('票据'));
        $grid->column('remarks', __('备注'))->editable();
        $grid->column('status', __('状态'))->switch();
        $grid->column('created_at', __('创建日期'))->display(function ($created_at){
            if(empty($created_at)){
                return '';
            }else{
                return date('Y-m-d H:i:s',strtotime($created_at));
            }
        });
        $grid->column('updated_at', __('更新日期'))->display(function ($updated_at){
            if(empty($updated_at)){
                return '';
            }else{
                return date('Y-m-d H:i:s',strtotime($updated_at));
            }
        });

        $grid->model()->orderBy('id', 'desc');
        $grid->actions(function ($actions){
            // 去掉删除
            // $actions->disableDelete();
            // 去掉编辑
            $actions->disableEdit();
            // 去掉查看
            $actions->disableView();
        });

        $grid->filter(function ($filter) use($house_list,$project_name_arr,$handler_arr){

            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->expand();//默认展开搜索栏

            $filter->column(5/10, function ($filter) use($project_name_arr) {
                $filter->in('type', '类型')->multipleSelect(['1'=>'收入','2'=>'支出']);
                $filter->between('date', '日期')->date();

                $filter->in('project_name', '项目名')->multipleSelect($project_name_arr);
                $filter->like('des_detail', '明细关键字检索');
                // 设置created_at字段的范围查询
                // $filter->between('created_at', '创建时间')->datetime();
            });

            $filter->column(5/10, function ($filter) use($house_list,$handler_arr){

                $filter->in('house_id', '房间现名')->multipleSelect($house_list);
                $filter->like('house_name', '房间快照名检索');
                $filter->equal('amount', '金额');
                $filter->in('handler', '经手人')->multipleSelect($handler_arr);
                $filter->like('remarks', '备注检索查询');
                $filter->in('status','状态')->checkbox([
                    '1'    => '启用',
                    '0'    => '未启用',
                ]);
                // $filter->like('data_name', '客户姓名');
                // $filter->like('data_json', '源数据查询');
                // $filter->between('updated_at', '更新时间')->datetime();
            });


        });

        $grid->export(function ($export) {

            $export->filename(time().'-'.date('Y-m-d').'-导出收入支出表.csv');

            $export->except(['bill',]);

            // $export->only(['column3', 'column4' ...]);

            $export->originalValue(['des_detail', 'house_name','amount','handler','remarks']);

            $export->column('status', function ($value, $original) {
                if ($original == '1'){
                    return '启用';
                }elseif ($original == '0'){
                    return '未启用';
                }else{
                    return '未知状态';
                }

            });
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(RevenueExpenses::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('type', __('Type'));
        $show->field('date', __('Date'));
        $show->field('project_name', __('Project name'));
        $show->field('des_detail', __('Des detail'));
        $show->field('house_id', __('House id'));
        $show->field('house_name', __('House name'));
        $show->field('amount', __('Amount'));
        $show->field('handler', __('Handler'));
        $show->field('bill', __('Bill'));
        $show->field('remarks', __('Remarks'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new RevenueExpenses());

        $house_data = DB::table('house')->get()->toarray();
        $house_list = [];
        foreach ($house_data as $key=>$house){
            $house_list[$house->id] = $house->community.$house->household.$house->room_number;
        }
        $project_list = DB::table('project')->where('status','=','1')->get();
        $project_name_arr = [];
        foreach ($project_list as $single_project) {
            $project_name_arr[$single_project->name] = $single_project->name;
        }

        $form->switch('status','状态');
        $states = [
            'on'  => ['value' => 1, 'text' => '同步', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '不同步', 'color' => 'danger'],
        ];

        $form->switch('tenant_status','是否为租金延续时长')->states($states);
        $form->radioButton('type', '类型')
            ->options([
                1 => '收入',
                2 => '支出',
            ])->when(1, function (Form $form) use($house_list,$project_name_arr) {

                //收入
                $form->radioButton('a', '选择或者填写房间')
                    ->options([
                        0 => '填写未记录房间',
                        1 => '选择已记录房间',
                    ])->when(1, function (Form $form) use($house_list) {

                        $form->select('house_id', __('房间现名'))->options($house_list);

                    })->when(0, function (Form $form) {

                        $form->text('house_name', __('房间快照名'));
                    })->default(1);
                $form->date('date', __('收入日期'))->default(date('Y-m-d'));
                $form->select('project_name', __('收入项目名'))->options($project_name_arr);
                $form->text('des_detail', __('收入明细'));

                $form->text('amount', __('收入金额'))->icon('fa-cny');
                $form->text('handler', __('经手人'))->icon('fa-male');
                // $form->text('bill', __('票据'));
                $form->tags('remarks', __('备注'));

            })->when(2, function (Form $form) use($house_list,$project_name_arr) {

                //支出
                $form->radioButton('a', '选择或者填写房间')
                    ->options([
                        0 => '填写未记录房间',
                        1 => '选择已记录房间',
                    ])->when(1, function (Form $form) use($house_list) {

                        $form->select('house_id', __('房间现名'))->options($house_list);
                        // $form->hidden('house_name', __('房间快照名'));

                    })->when(0, function (Form $form) {

                        $form->text('house_name', __('房间快照名'));
                        // $form->hidden('house_id', __('房间现名'));

                    })->default(1);
                $form->date('date', __('支出日期'))->default(date('Y-m-d'));
                $form->select('project_name', __('收入项目名'))->options($project_name_arr);
                $form->text('des_detail', __('支出明细'));



                $form->text('amount', __('支出金额'))->icon('fa-cny');
                $form->text('handler', __('经手人'))->icon('fa-male');
                // $form->text('bill', __('票据'));
                $form->tags('remarks', __('备注'));

            });

        $form->ignore(['a',]);
        $form->saving(function (Form $form) use($house_list){
            //类型为收入
            if ($form->house_id){
                // 已选房间号
                $form->house_name = $house_list[$form->house_id];
                // var_dump($form->house_id);
            }else{
                // 未选房间号
                $form->house_id = 0;
            }
            // var_dump($form->tenant_status);

        });
        //保存后回调
        $form->saved(function (Form $form) {
            if ($form->model()->tenant_status == '1'){
                //租房日期需要延长
                // $form->model()->house_id
                $house_data = DB::table('tenant')->where('house_id',$form->model()->house_id)
                    ->where('contract_period','>',date('Y-m-d'))
                    ->get()->toarray();
                if(empty($house_data)){
                    //并没有合适的租金租房记录
                    $success = new MessageBag([
                        'title'   => '保存成功',
                        'message' => '没有合适的租金租房记录',
                    ]);
                }else{
                    //找到了记录
                    //判断记录的条数
                    if (count($house_data) == '1'){
                        //找到了记录并且唯一，上面还确定需要去更新续约，判断是否整除，然后加几个月的日期
                        $multiple = ($form->model()->amount)/($house_data[0]->monthly_rent);
                        if (ceil($multiple)==$multiple){ //**********
                            //整除为整数,直接续租几个月
                            if(empty($house_data[0]->fixed_date)){
                                $new_fixed_date = date("Y-m-d",strtotime("+".$multiple." month"));
                            }else{
                                $new_fixed_date = date("Y-m-d",strtotime("+".$multiple." month",strtotime($house_data[0]->fixed_date)));
                            }
                            $update_status =  DB::table('tenant')->where('id',$house_data[0]->id)->update(['fixed_date'=>$new_fixed_date]);
                            if($update_status){
                                $success = new MessageBag([
                                    'title'   => '保存成功',
                                    'message' => '租房租金日期续约成功',
                                ]);
                            }else{
                                $success = new MessageBag([
                                    'title'   => '保存成功',
                                    'message' => '租房租金日期续约失败，手动修改',
                                ]);
                            }

                        }else{
                            //不为整数，直接结束
                            $success = new MessageBag([
                                'title'   => '保存成功',
                                'message' => '收入不为租金的倍数，不做处理',
                            ]);
                        }


                    }else{
                        //找到了记录但是不唯一
                        $success = new MessageBag([
                            'title'   => '保存成功',
                            'message' => '续约日期未更新，符合条件记录不唯一',
                        ]);
                    }
                }


            }else{
                //租房日期没有关系
                $success = new MessageBag([
                    'title'   => '保存成功',
                    'message' => '保存成功',
                ]);
            }

            return back()->with(compact('success'));
        });
        return $form;
    }
}
