<md-card>
    <md-card-title>
        <md-card-title-text>
        <span class="md-headline" ng-bind-html="question.title"></span>
        </md-card-title-text>
    </md-card-title>
    <md-card-content>
        <div ng-repeat="cQuestion in question.childrens">
            <md-checkbox ng-model="answers[cQuestion.id]" ng-true-value="'{{answer.id}}'" ng-false-value="null" ng-change="save_answers(cQuestion)" class="md-primary">
                {{ cQuestion.title }}
            </md-checkbox>
        </div>
    </md-card-content>
</md-card>
