<div style="margin: 10px 0">
    <div class="ui mini steps">
        <div class="<?php echo $step > 0 ? 'completed':''?> step <?php echo $step==0 ? 'active':''?>">
            <i class="edit icon"></i>
            <div class="content">
                <div class="title">編製加掛問卷</div>
            </div>
        </div>
        <div class="<?php echo $step > 1 ? 'completed':''?> step <?php echo $step==1 ? 'active':''?>">
            <i class="unhide icon"></i>
            <div class="content">
                <div class="title">確認加掛問卷</div>
            </div>
        </div>
        <div class="<?php echo $step > 2 ? 'completed':''?> step <?php echo $step==2 ? 'active':''?>">
            <i class="setting icon"></i>
            <div class="content">
                <div class="title">設定申請資料</div>
            </div>
        </div>
        <div class="<?php echo $step > 3 ? 'completed':''?> step <?php echo $step==3 ? 'active':''?>">
            <i class="checkmark box icon"></i>
            <div class="content">
                <div class="title">資料審核</div>
            </div>
        </div>
    </div>
</div>
