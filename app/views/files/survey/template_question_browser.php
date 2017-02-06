
<table class="ui celled structured table browserTable" style="font-family: Microsoft JhengHei">
        <tr style="font-size: 18px;">
            <td><b>題號</b></td>
            <td><b>題型</b></td>
            <td><b>題目</b></td>
            <td><b>選項</b></td>
        </tr>
        <tr ng-repeat="(key,question) in questions">
            <!-- 題號 -->
            <td  ng-if="checkRowspanLenth(question)"  rowspan="{{question.rowspan}}" style="text-align: center">{{question.question_number}}</td>
            <!-- 題型 -->
            <td  ng-if="checkRowspanLenth(question)"  rowspan="{{question.rowspan}}" >{{question.node.type}}</td>
            <!-- 題目 -->
            <td  ng-click="showPassQuestion(question)" ng-if="checkRowspanLenth(question)" rowspan="{{question.rowspan}}">
                {{question.question_title}}
                <span ng-if="question.rules.length > 0" class="ui left pointing red basic label">{{question.rules.length}}個跳題條件</span>
            </td>
            <!-- 答案 -->
            <td>
                <span ng-repeat="(key,answer) in question.node.answers">{{key+1}}.{{answer.title}}&nbsp&nbsp</span>
                <span ng-if="!checkQuestionType(question)">{{question.title}}</span> <!-- 只印出複選題選項 -->
            </td>
        </tr>
</table>