<md-dialog aria-label="加掛題申請表" ng-cloak>
    <md-dialog-content layout="column"  layout-align="start center">
        <div style="width: 960px">
            <md-card>
                <md-card-header md-colors="{background: 'indigo'}">
                    <md-card-header-text>
                        <span class="md-title">加掛題申請表</span>
                    </md-card-header-text>
                </md-card-header>
                <md-content>
                    <md-list flex>
                        <md-subheader class="md-no-sticky"><h4>變向選擇</h4></md-subheader>
                        <md-list-item ng-repeat="column in columns" ng-if="edited">
                            <p>{{column.survey_applicable_option.title}}</p>
                        </md-list-item>
                        <md-list-item ng-if="!edited">
                            <p>無申請項目</p>
                        </md-list-item>
                        <md-divider ></md-divider>
                        <md-subheader class="md-no-sticky"><h4>主題本資料串聯申請</h4></md-subheader>
                        <md-list-item ng-repeat="question in questions" ng-if="edited">
                            <p>{{question.survey_applicable_option.title}}</p>
                        </md-list-item>
                        <md-list-item ng-if="!edited">
                            <p>無申請項目</p>
                        </md-list-item>
                    </md-list>
                </md-content>
            </md-card>
        </div>
    </md-dialog-content>
</md-dialog>
