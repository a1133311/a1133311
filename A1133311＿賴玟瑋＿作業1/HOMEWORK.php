<HTML>
<HEAD>
    <TITLE>2026 勁羽先鋒 - 羽球夏令營報名表</TITLE>
</HEAD>
<BODY BACKGROUND="bg_faded.jpg" BGCOLOR="#455A40" TEXT="WHITE">

    <BR><BR>
    <CENTER>
        <TABLE BORDER="0" WIDTH="600">
            <TR>
                <TD ALIGN="CENTER">
                    <H1><FONT COLOR="YELLOW">🏸 2026 資管羽球夏令營報名表</FONT></H1>
                    <HR WIDTH="80%" COLOR="YELLOW">
                </TD>
            </TR>
        </TABLE>
    </CENTER>

    <TABLE BORDER="0" ALIGN="CENTER" WIDTH="600" CELLPADDING="10" CELLSPACING="0">
        <FORM ACTION="submit.php" METHOD="POST">
            
            <TR BGCOLOR="#5D7A56">
                <TD COLSPAN="2"><B><FONT SIZE="4">【學員基本資料】</FONT></B></TD>
            </TR>
            
            <TR>
                <TD WIDTH="200" ALIGN="RIGHT">學員姓名：</TD>
                <TD><INPUT TYPE="TEXT" NAME="student_name" SIZE="30"></TD>
            </TR>
            <TR>
                <TD ALIGN="RIGHT">性別：</TD>
                <TD>
                    <INPUT TYPE="RADIO" NAME="gender" VALUE="male"> 男
                    <INPUT TYPE="RADIO" NAME="gender" VALUE="female"> 女
                </TD>
            </TR>
            <TR>
                <TD ALIGN="RIGHT">出生年月日：</TD>
                <TD><INPUT TYPE="DATE" NAME="birthday"></TD>
            </TR>

            <TR><TD COLSPAN="2"><BR></TD></TR>

            <TR BGCOLOR="#5D7A56">
                <TD COLSPAN="2"><B><FONT SIZE="4">【羽球程度與裝備】</FONT></B></TD>
            </TR>
            <TR>
                <TD ALIGN="RIGHT">程度自我評估：</TD>
                <TD>
                    <SELECT NAME="skill_level">
                        <OPTION VALUE="newcomer">新手階</OPTION>
                        <OPTION VALUE="beginner">初階</OPTION>
                        <OPTION VALUE="intermediate">初中階</OPTION>
                        <OPTION VALUE="advanced">中階</OPTION>
                        <OPTION VALUE="expert">中進階</OPTION>
                        <OPTION VALUE="master">高階</OPTION>
                        <OPTION VALUE="professional">職業級</OPTION>
                    </SELECT>
                    <BR>
                    <A HREF="https://sites.google.com/view/ylbt/%E7%BE%BD%E7%90%83%E5%88%86%E7%B4%9A%E8%A1%A8" TARGET="_blank" STYLE="text-decoration:none;">
                        <FONT SIZE="2" COLOR="#87CEEB"><u>按此查看羽球分級參考表</u></FONT>
                    </A>
                </TD>
            </TR>
            <TR>
                <TD ALIGN="RIGHT">慣用手：</TD>
                <TD>
                    <INPUT TYPE="RADIO" NAME="hand" VALUE="right"> 右手
                    <INPUT TYPE="RADIO" NAME="hand" VALUE="left"> 左手
                </TD>
            </TR>
            <TR>
                <TD ALIGN="RIGHT">球拍：</TD>
                <TD>
                    <INPUT TYPE="RADIO" NAME="buy_racket" VALUE="yes"> 代購 
                    <INPUT TYPE="RADIO" NAME="buy_racket" VALUE="no"> 自備
                </TD>
            </TR>

            <TR><TD COLSPAN="2"><BR></TD></TR>

            <TR>
                <TD ALIGN="RIGHT" VALIGN="TOP">備註事項：</TD>
                <TD><TEXTAREA NAME="note" ROWS="4" COLS="40" PLACEHOLDER="如有特殊需求請在此註明"></TEXTAREA></TD>
            </TR>

            <TR>
                <TD COLSPAN="2" ALIGN="CENTER">
                    <HR COLOR="#5D7A56">
                    <FONT COLOR="#FFBABA">※ 請再次確認報名資訊</FONT> <BR><BR>
                    <INPUT TYPE="SUBMIT" VALUE="   送出報名   " STYLE="padding:5px 20px; cursor:pointer;">
                </TD>
            </TR>
            
        </FORM>
    </TABLE>

    <BR><BR>
    <HR WIDTH="600">
    <CENTER>
        <A HREF="http://www.nuk.edu.tw"><FONT COLOR="WHITE">回國立高雄大學首頁</FONT></A>
    </CENTER>
    <BR><BR>

</BODY>
</HTML>