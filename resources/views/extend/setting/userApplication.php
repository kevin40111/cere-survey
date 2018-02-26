<md-dialog aria-label="檢視申請表" class="demo-dialog-example">
    <md-toolbar>
        <div class="md-toolbar-tools">
            <h2>檢視申請表</h2>
        </div>
    </md-toolbar>
    <md-dialog-content ng-cloak class="demo-dialog-content">
        <div layout="column" style="font-size:1em; color:grey; margin:15px;" layout-align="center start">
            <div layout="row">
                <md-icon>adjust</md-icon>
                <div ng-repeat="organization in member.organizations" layout="row">加掛學校: {{ organization.now.name }} </div>
            </div>
            <div layout="row" style="margin-top:8px;">
                <md-icon>account_circle</md-icon>
                <div>承辦人: {{member.user.username}} </div>
                <div>&emsp;Email: {{member.user.email}} </div>
                <div>&emsp;電話: {{member.contact.tel}}</div>
            </div>

        </div>
        <md-card style="margin:20px 50px 20px 50px; font-family:Microsoft JhengHei">

            <md-card-title>
                <md-card-title-text md-colors="{color:'default-indigo'}">
                    <md-title>可申請的母體名單數量:&emsp;{{columnsLimit}}</md-title>
                </md-card-title-text>
            </md-card-title>
            <md-card-title>
                <md-card-title-text md-colors="{color:'default-indigo'}">
                    <md-title>已申請的母體名單欄位</md-title>
                </md-card-title-text>
            </md-card-title>
            <md-card-content>
                <md-list-item ng-repeat="column in columns">
                    {{column.title}}
                </md-list-item>
            </md-card-content>
            <md-divider></md-divider>
            <md-card-title>
                <md-card-title-text md-colors="{color:'default-indigo'}">
                    <md-title>可加入的主問卷之題目欄位的數量:&emsp;{{fieldsLimit}}</md-title>
                </md-card-title-text>
            </md-card-title>
            <md-card-title>
                <md-card-title-text md-colors="{color:'default-indigo'}">
                    <md-title>已加入的主問卷之題目欄位</md-title>
                </md-card-title-text>
            </md-card-title>
            <md-card-content>
                <md-list flex>
                    <md-subheader class="md-no-sticky" ng-repeat-start="page in pages">母體問卷第{{$index+1}}頁</md-subheader>
                    <md-list-item ng-repeat-end ng-repeat="question in page">
                        {{$index+1}}. {{question.title}}
                    </md-list-item>
                </md-list>
            </md-card-content>
        </md-card>
    </md-dialog-content>
    <md-dialog-actions style="color:grey" layout="row">
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