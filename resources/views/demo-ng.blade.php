
<script type="text/ng-template" id="list">
    @include('survey::template_question_list')
</script>
<script type="text/ng-template" id="checkbox">
    @include('survey::template_question_checkbox')
</script>
<script type="text/ng-template" id="select">
    @include('survey::template_question_select')
</script>
<script type="text/ng-template" id="radio">
    @include('survey::template_question_radio')
</script>
<script type="text/ng-template" id="scale">
    @include('survey::template_question_scale')
</script>
<script type="text/ng-template" id="text">
    @include('survey::template_question_text')
</script>
<script type="text/ng-template" id="number">
    @include('survey::template_question_number')
</script>
<script type="text/ng-template" id="gear">
    @include('survey::template_question_gear')
</script>

<script src="/packages/cere/survey/js/ng/ngSurvey.js"></script>

<script>
app.requires.push('ngSurvey');
app.controller('quesController', function() {});
</script>

<link rel="stylesheet" href="/packages/cere/survey/js/quill.snow.min.css">
<link rel="stylesheet" href="/packages/cere/survey/js/quill.bubble.min.css">
<style></style>

<div ng-cloak flex layout="column" ng-controller="quesController">
    <survey-book flex layout="column"></survey-book>
</div>
