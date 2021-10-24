<?php

namespace App\Admin\Controllers\Rent;

use App\Models\Tenant;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;

class TenantController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '租客信息';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Tenant());

        $house_data = DB::table('house')->get()->toarray();
        $house_list = [];
        foreach ($house_data as $key=>$house){
            $house_list[$house->id] = $house->community.$house->household.$house->room_number;
        }
        $grid->column('id', __('编号'));
        $grid->column('house_id', __('房间现用名'))->display(function ($house_id) use($house_list)  {
            return $house_list[$house_id];
        });
        $grid->column('house_backup', __('房间快照名'));
        $grid->column('contract_date', __('签约日期'));
        $grid->column('contract_period', __('到期日期'));
        $grid->column('monthly_rent', __('月租金'));
        $grid->column('water_rent', __('水费'));
        $grid->column('electricity_rent', __('电费'));
        $grid->column('payment_method', __('支付方式'));
        $grid->column('username', __('租客名字'));
        $grid->column('phone', __('租客手机号'));
        $grid->column('id_code', __('租客身份证号'));
        $grid->column('resident_information', __('房间居住人信息集合'));
        $grid->column('prompt_pre_days', __('提前几日提示交租时间'));
        $grid->column('fixed_date', __('或固定提示交租时间'));
        $grid->column('deposit', __('押金'));
        $grid->column('created_at', __('创建时间'))->display(function ($created_at){
            if(empty($created_at)){
                return '';
            }else{
                return date('Y-m-d H:i:s',strtotime($created_at));
            }
        });
        $grid->column('updated_at', __('更新时间'))->display(function ($updated_at){
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
            // $actions->disableEdit();
            // 去掉查看
            $actions->disableView();
        });
        $grid->filter(function ($filter) use($house_list){

            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->expand();//默认展开搜索栏

            $filter->column(5/10, function ($filter) use($house_list) {
                $filter->in('house_id', '房间现名')->multipleSelect($house_list);
                $filter->like('house_backup', '房间快照名检索');
                $filter->between('contract_date', '签约日期')->date();
                $filter->between('contract_period', '到期日期')->date();
                // 设置created_at字段的范围查询
                // $filter->between('created_at', '创建时间')->datetime();
            });

            $filter->column(5/10, function ($filter) use($house_list){
                $filter->like('username', '租客姓名');
                $filter->like('phone', '租客手机');
                $filter->between('fixed_date', '提示收租日期')->date();
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
        $show = new Show(Tenant::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('house_id', __('House id'));
        $show->field('house_backup', __('House backup'));
        $show->field('contract_date', __('Contract date'));
        $show->field('contract_period', __('Contract period'));
        $show->field('monthly_rent', __('Monthly rent'));
        $show->field('water_rent', __('Water rent'));
        $show->field('electricity_rent', __('Electricity rent'));
        $show->field('payment_method', __('Payment method'));
        $show->field('username', __('Username'));
        $show->field('phone', __('Phone'));
        $show->field('id_code', __('Id code'));
        $show->field('resident_information', __('Resident information'));
        $show->field('prompt_pre_days', __('Prompt pre days'));
        $show->field('fixed_date', __('Fixed date'));
        $show->field('deposit', __('Deposit'));
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
        $form = new Form(new Tenant());

        $house_data = DB::table('house')->get()->toarray();
        $house_list = [];
        foreach ($house_data as $key=>$house){
            $house_list[$house->id] = $house->community.$house->household.$house->room_number;
        }

        $form->select('house_id', __('房间号'))->options($house_list);
        $form->hidden('house_backup','房间快照名');
        $form->date('contract_date', __('签约开始日期'))->default(date('Y-m-d'));
        $form->date('contract_period', __('签约结束日期'))->default(date('Y-m-d',strtotime('+1year')));
        $form->text('monthly_rent', __('月租金'));
        $form->text('water_rent', __('水费租金'));
        $form->text('electricity_rent', __('电费租金'));
        $form->number('payment_method', __('付款方式（填数字）'));
        $form->text('username', __('租客姓名'));
        $form->mobile('phone', __('租客手机'));
        $form->text('id_code', __('身份证号'));
        $form->text('resident_information', __('居住信息集合'));
        $form->number('prompt_pre_days', __('提前几日通知'));
        $form->date('fixed_date', __('固定日期通知（收入记录，可更新）'))->default(date('Y-m-d'));
        $form->text('deposit', __('押金'));

        $form->saving(function (Form $form) use($house_list){
            $form->house_backup = $house_list[$form->house_id];
        });



        return $form;
    }
}
