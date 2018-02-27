<md-dialog aria-label="檢視申請表" class="demo-dialog-example">
    <md-toolbar>
        <div class="md-toolbar-tools">
            <h2>檢視申請表</h2>
        </div>
    </md-toolbar>
    <md-dialog-content ng-cloak layout="column" class="demo-dialog-content">
        <div layout="column" layout-padding style="font-size:1em; color:grey" layout-align="center start">
            <div layout="row">
                <md-icon>adjust</md-icon>
                <div>加掛學校: <span ng-repeat-start="organization in member.organizations">{{ organization.now.name }}</span><span ng-repeat-end ng-if="!$last">、</span></div>
            </div>
            <div layout="row">
                <md-icon>account_circle</md-icon>
                <div>承辦人: {{member.user.username}} </div>
                <div>&emsp;Email: {{member.user.email}} </div>
                <div>&emsp;電話: {{member.contact.tel}}</div>
            </div>
        </div>
        <md-progress-linear md-mode="indeterminate" ng-disabled="!loading"></md-progress-linear>
        <md-content flex ng-if="!loading">
            <md-subheader class="md-primary">已申請的母體名單欄位 (可申請數量 {{hook.main_list_limit.amount}})</md-subheader>
            <md-list>
                <md-list-item ng-repeat="field in mainListFields">
                    {{field.title}}
                </md-list-item>
            </md-list>
            <md-divider></md-divider>
            <md-subheader class="md-primary">已申請的主問卷之題目欄位 (可申請數量 {{hook.main_book_limit.amount}})</md-subheader>
            <md-list>
                <md-subheader class="md-no-sticky" ng-repeat-start="page in mainBookPages">母體問卷第{{$index+1}}頁</md-subheader>
                <md-list-item ng-repeat-end ng-repeat="field in page.fields">
                    {{$index+1}}. {{field.title}}
                </md-list-item>
            </md-list>
        </md-content>
    </md-dialog-content>
    <md-dialog-actions layout="row">
        <!-- <md-button aria-label="申請表意見" class="md-primary"><md-icon md-svg-icon="assignment"></md-icon><span>申請表意見</span></md-button> -->
        <span flex="5"></span>
        <md-input-container class="md-block" style="width:150px;">
            <label>申請表審核</label>
            <md-select ng-model="individual_status.apply" ng-change="updateIndividualStatus()">
                <md-option ng-repeat="(key,status) in selectStatus" ng-value="key">{{status.title}}</md-option>
            </md-select>
        </md-input-container>
    </md-dialog-actions>
</md-dialog>
