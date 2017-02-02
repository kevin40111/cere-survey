<table class="ui celled structured table browserTable">
        <tr>
            <td><b>題型</b></td>
            <td><b>題目</b></td>
            <td><b>選項</b></td>
        </tr>
        <tr ng-repeat="(key,question) in questions">
            <!-- 題型 -->
            <td ng-if="checkRowspan(question)"  rowspan="{{question.rowspan}}">{{question.node.type}}</td>
            <!-- 題目 -->
            <td ng-if="checkRowspan(question)" rowspan="{{question.rowspan}}">{{question.question_title}}</td>
            <!-- 答案 -->
            <td>
                <span ng-repeat="(key,answer) in question.node.answers">
                 <i>{{key+1}}.</i>{{answer.title}}
                </span>
                <span ng-if="!checkType(question)">{{question.title}}</span>
            </td>
        </tr>
</table>