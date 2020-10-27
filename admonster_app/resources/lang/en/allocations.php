<?php
/**
 * Created by PhpStorm.
 * User: yonghua-you
 * Date: 2019/01/11
 * Time: 11:23
 */
return [
    'individual_assignment' => 'individual assignment',
    'back_list' => 'back to OrderList',
    'concurrency' => 'failed to reach',
    'fail_reach' => 'intercurrent degree',
    'other_user_updating' => 'other users is updating the data',
    'error_message' => 'An internal error occurred',
    'order_detail' => [
        'headers' => [
            'order_name' => 'order name',
            'current_work' => 'current work',
            'process' => 'process',
            'operator' => 'operator',
            'createdAt' => 'date of onset',
            'deadline' => 'deadline of delivery'
        ],
        'processes' => [
            'allocation' => 'assignment',
            'work' => 'work',
            'approval' => 'approval',
            'delivery' => 'delivery',
            'finished' => 'finished',
        ],

    ],
    'request_content' => [
        'odditional_file' => ' items of file appended',

    ],
    'allocation_list'=>[
        'operator' => 'operator',
        'search' => 'search',
        'allocation'=>'assignment',
        'headers'=>[
            'user_name'=>'operator',
            'work_in_process_count'=>'Remaining works(items)',
            'work_in_process_count_detail'=>'the number of Remaining works which is assigning at present',
            'estimated_time'=>'finished probable time(minutes)',
            'estimated_time_detail'=>'the probable time for all work assigned at present',
            'completed_count'=>'achievement(items)',
            'completed_count_detail'=>'the number of the achievement assigned by this operator',
            'average'=>'average(minutes)',
            'average_detail'=>'the average working time of this operator',
            'percentage'=>'Accuracy(%)',
            'percentage_detail'=>"operator's Accuracy "
        ],
        'remind_dialog'=>[
          'icon_name'=>'set remind'
        ],

    ],


];
