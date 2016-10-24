<md-input-container ng-repeat="question in node.questions" class="md-block">
    <label>{{question.title}}</label>
    <input ng-model="answers[question.id]" type="text">
</md-input-container>