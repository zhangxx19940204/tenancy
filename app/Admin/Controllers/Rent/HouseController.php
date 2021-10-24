<?php

namespace App\Admin\Controllers\Rent;

use App\Models\House;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class HouseController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '房间列表';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new House());
        $user_obj = Auth::guard('admin')->user();

        $grid->header(function ($query) {
            $user_obj = Auth::guard('admin')->user();

            return '用户ID：'.$user_obj->id;
        });

        $grid->column('id', __('编号'));
        $grid->column('community', __('小区'));
        $grid->column('household', __('户'));
        $grid->column('room_number', __('房间号'));
        $grid->column('status', __('状态'))->switch();
        // $grid->column('is_enable', __('租用情况'))->using([
        //     0 => '已租',
        //     1 => '待租',
        // ], '未知')->dot([
        //     0 => 'danger',
        //     1 => 'info',
        // ], 'warning');
        $grid->column('compact_file', __('合同文件等'));
        $grid->column('remarks', __('备注'))->editable();
        $grid->column('created_at', __('创建时间'))->display(function ($created_at){
            if(empty($created_at)){
                return '';
            }else{
                return date('Y-m-d H:i:s',strtotime($created_at));
            }
        });
        $grid->column('updated_at', __('修改时间'))->display(function ($updated_at){
            if(empty($updated_at)){
                return '';
            }else{
                return date('Y-m-d H:i:s',strtotime($updated_at));
            }
        });

        $grid->model()->orderBy('id', 'desc');

        $grid->actions(function ($actions){
            // 去掉查看
            $actions->disableView();
        });
        $grid->filter(function ($filter) {

            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->expand();//默认展开搜索栏
            $filter->column(5/10, function ($filter) {

                // $filter->in('config_id', '账户信息')->multipleSelect($config_arr);
                // $project_list = DB::table('house')->distinct('community')->get();
                // $project_arr = [];
                // foreach ($project_list as $single_project) {
                //     $project_arr[$single_project->project_name] = $single_project->project_name;
                // }
                //
                // $filter->in('belong', '所属')->multipleSelect($project_arr);
                //
                // // 设置created_at字段的范围查询
                // $filter->between('created_at', '创建时间')->datetime();
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
        $show = new Show(House::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('community', __('Community'));
        $show->field('household', __('Household'));
        $show->field('room_number', __('Room number'));
        $show->field('compact_file', __('Compact file'));
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
        $form = new Form(new House());

        $form->text('community', __('小区名'));
        $form->text('household', __('户'));
        $form->text('room_number', __('房间号'));
        $form->switch('status', __('状态'));
        $form->tags('remarks', __('备注'));
        return $form;
    }
}
