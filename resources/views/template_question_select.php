<md-card-content>
    <md-input-container ng-repeat="question in node.questions">
        <label>{{question.title}}</label>
        <md-select ng-model="contents[question.id]" ng-disabled="node.saving" placeholder="請選擇" ng-change="sync()">
            <md-option ng-repeat="answer in node.answers" ng-value="answer.value" ng-disabled="isSkip(answer)">{{answer.title}}</md-option>
        </md-select>
        <div style="padding-left: 5px">
            <survey-node ng-repeat="children in node.childrens[question.id]" node="children"></survey-node>
        </div>
    </md-input-container>
</md-card-content>