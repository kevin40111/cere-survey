<md-card>
    <md-card-title>
        <md-card-title-text>
        <span class="md-headline" ng-bind-html="question.title"></span>
        </md-card-title-text>
    </md-card-title>
    <md-card-content>
        <md-select ng-model="answers[question.id]" placeholder="請選擇">
            <md-option ng-repeat="answer in question.answers" ng-value="answer.id">{{answer.title}}</md-option>
        </md-select>
    </md-card-content>
</md-card>