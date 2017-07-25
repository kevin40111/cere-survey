<div>
    <h1 class="ui header" ng-bind-html="question.title"></h1>
    <div ng-repeat="bQuestion in branchs | filter:{parent_question_id:question.id}:true" question="bQuestion" branchs="branchs" childrens="childrens"></div>
</div>