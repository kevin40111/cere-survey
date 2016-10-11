<md-card>
    <md-card-title>
        <md-card-title-text>
        <span class="md-headline" ng-bind-html="question.title"></span>
        </md-card-title-text>
    </md-card-title>
    <md-card-content>
        <div layout="column">
            <div layout="row" layout-padding>
                <div flex></div>
                <div flex style="max-width:65px" ng-repeat="answer in question.answers">{{answer.title}}</div>
            </div>
            <md-radio-group ng-model="answers[cQuestion.id]" ng-repeat="cQuestion in question.childrens">
                <div layout="row" layout-padding>
                    <div flex>{{cQuestion.title}}</div>            
                    <div flex style="max-width:65px" ng-repeat="answer in question.answers" layout="column" layout-align="start center">            
                        <md-radio-button ng-value="answer.id" aria-label="{{answer.title}}"></md-radio-button>
                    </div>
                </div>
            </md-radio-group>
        </div>
    </md-card-content>
</md-card>