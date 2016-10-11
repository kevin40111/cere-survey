<md-card>
    <md-card-title>
        <md-card-title-text>
        <span class="md-headline" ng-bind-html="question.title"></span>
        </md-card-title-text>
    </md-card-title>
    <md-card-content>
        <md-input-container ng-repeat="cQuestion in question.childrens" class="md-block">
            <label>{{cQuestion.title}}</label>
            <input ng-model="answers[cQuestion.id]" type="text">
        </md-input-container>
    </md-card-content>
</md-card>