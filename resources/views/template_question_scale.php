<md-card-content layout="column">
    <div layout="row" layout-padding>
        <div flex></div>
        <div flex style="max-width:65px" ng-repeat="answer in node.answers">{{answer.title}}</div>
    </div>
    <md-radio-group ng-model="contents[question.id]" ng-disabled="isSkip(question)" ng-repeat="question in node.questions" ng-change="sync()">
        <div layout="row" layout-padding>
            <div flex>{{question.title}}</div>
            <div flex style="max-width:65px" ng-repeat="answer in node.answers" layout="column" layout-align="start center">
                <md-radio-button ng-value="answer.value" aria-label="{{answer.title}}" ng-disabled="isSkip(answer)"></md-radio-button>
            </div>
        </div>
    </md-radio-group>
</md-card-content>