<md-card>
    <md-card-title>
        <md-card-title-text>
        <span class="md-headline" ng-bind-html="question.title"></span>
        </md-card-title-text>
    </md-card-title>
    <md-card-content>
        <md-radio-group ng-model="answers[question.id]" ng-change="save_answers(question)">
            <md-radio-button ng-repeat="answer in question.answers" value="{{answer.id}}" class="md-primary">{{answer.title}}</md-radio-button>
        </md-radio-group>
    </md-card-content>
</md-card>