/*
multiup.as Source file for flash used in filebox.php. Multiple files uploader.
Copyright (C) 2009 National Research Institute for Earth Science and
Disaster Prevention (NIED)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 3.x of the License.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

(参考）上記の日本語訳
multiup.as filebox.phpから呼び出される、複数ファイルを一括アップロードするためのフラッシュのソース。
Copyright (C) 2009 独立行政法人防災科学技術研究所

このプログラムはフリーソフトウェアです。
あなたはこれを、フリーソフトウェア財団によって発行された
GNU 一般公衆利用許諾契約書
(バージョン3.xか、それ以降のバージョン)の定める条件の下で
再頒布または改変することができます。

このプログラムは有用であることを願って頒布されますが、
『全くの無保証』です。
商業可能性の保証や特定の目的への適合性は、
言外に示されたものも含め全く存在しません。
詳しくはGNU 一般公衆利用許諾契約書をご覧ください。

あなたはこのプログラムと共に、
GNU 一般公衆利用許諾契約書の複製物を一部
受け取ったはずです。
もし受け取っていなければ、
フリーソフトウェア財団まで請求してください
(宛先は the Free Software Foundation, Inc., 59
Temple Place, Suite 330, Boston, MA 02111-1307 USA)。
*/
package {
    import flash.display.*;
    import flash.text.*;
    import flash.net.*;
    import flash.events.*;
   [SWF(width=600, height=260, backgroundColor=0xFFFFFF)]

    public class multiup extends Sprite{
        private var req:URLRequest;
        private var messageField:TextField = new TextField();
        private var messageField2:TextField = new TextField();
        private var frl:FileReferenceList = new FileReferenceList();
        private var uplbtn:SimpleButton;
        private var selbtn:SimpleButton;
	private var isUploading:Boolean;
	private var max_filesize:int;
	private var file_index:int = 0;
	private var num_errors:int = 0;

	//	private var enableFormat:textFormat = new TextFormat();
	//	private var disableFormat:textFormat = new TextFormat();

	private function btnDisable(btn:SimpleButton):void{
	    btn.enabled = false;
	    btn.hitTestState = null;
	    //	    btn.upState.setTextFormat = disableFormat;
	}
	private function btnEnable(btn:SimpleButton):void{
	    btn.enabled = true;
	    btn.hitTestState = btn.upState;
	    //	    btn.upState.setTextFormat = enableFormat;
	}
        private function fileSelected(e:Event):void{
            var s:String = new String();

            s = "アップロードファイル:\n";
	    for(var i:uint = 0; i<frl.fileList.length; i++){
		var file:FileReference = frl.fileList[i];
		if(max_filesize>0 && file.size > max_filesize){
		    s += "  "+file.name+"..サイズ制限超過\n";
		}else{
		    s += "  "+file.name+"\n";
		}
	    }
	    messageField.text = s+"---\n";

	    if(frl.fileList.length>0)
		btnEnable(uplbtn);
        }
	private function browse(e:MouseEvent):void{
            messageField.text = "ファイルを選択してください\n";
	    if(max_filesize>0)
		messageField.text += "ファイルサイズの制限は"+
		    max_filesize+"バイトです。";
            frl.addEventListener(Event.SELECT,fileSelected);
            if(!frl.browse()){
                messageField.text = "ファイル選択ダイアログの生成に失敗しました";
            }
	}
	private function upComplete(e:Event):void{
	    messageField.appendText(" 完了\n");
	}
	private function upload(e:MouseEvent):void{
            var num_errors:int = 0;
	    btnDisable(selbtn);
	    btnDisable(uplbtn);
	    
	    file_index = 0;
	    num_errors = 0;
	    upload2();
	}
	private function upload2():void{
	    if(file_index == frl.fileList.length){
		messageField2.text = String(frl.fileList.length-num_errors)+
		    " 個のファイルをアップロードしました。\n";
		if(num_errors>0)
		    messageField2.appendText(String(num_errors)+
					     " 個のファイルでエラーが発生しました。");
		btnEnable(selbtn);
		file_index = 0;
		num_errors = 0;
		return;
	    }

	    var file:FileReference = frl.fileList[file_index];
	    
	    if(max_filesize>0 && file.size > max_filesize){
		messageField.appendText(file.name+"..サイズ制限超過\n");
		file_index++;
		num_errors++;
		upload2();
		return;
	    }

	    messageField.appendText("アップロード中:"+file.name);
	    messageField2.text = "アップロード中 "+file.name+
		":"+file_index+"/"+frl.fileList.length;

	    file.addEventListener(Event.COMPLETE,
		function (e:Event):void{
                    messageField.appendText("..完了\n");
		    upload2();
		});
	    file.addEventListener(IOErrorEvent.IO_ERROR,
                function(e:Event):void{
                    messageField.appendText("..IOエラー\n");
		    upload2();
                });
	    file.addEventListener(SecurityErrorEvent.SECURITY_ERROR,
                function(e:SecurityErrorEvent):void{
                    messageField.appendText("..セキュリティエラー\n");
		    upload2();
                });
	    file.addEventListener(ProgressEvent.PROGRESS,
		function(event:ProgressEvent):void {
		    if(!isUploading)return;
	    		messageField2.text = "アップロード中:"+
			    event.target.name+":"+
			    int(event.bytesLoaded/event.bytesTotal*100)+'%';
		});

	    file_index++;
	    file.upload(req,"upload_file");
	}
	private function makebtn(s:String,width:int,height:int):SimpleButton{
	    var btn:SimpleButton = new SimpleButton();
	    var btntxt:TextField = new TextField();
	    var btntxt2:TextField = new TextField();
            btntxt.text=s;
	    btntxt.height=height;
	    btntxt.width=width;
	    btntxt.background=true;
	    btntxt.backgroundColor=0xC0C0C0;
	    btntxt.border=true;
	    btntxt.borderColor=0xC0C0C0;
	    btn.upState = btntxt;
            btntxt2.text=s;
	    btntxt2.height=height;
	    btntxt2.width=width;
	    btntxt2.background=true;
	    btntxt2.backgroundColor=0xC0C0C0;
	    btntxt2.border=true;
	    btntxt2.borderColor=0x000000;
	    btn.overState = btntxt2;
	    btn.hitTestState = btn.upState;
	    btn.useHandCursor = true;
	    return btn;
	}
        public function multiup (){
	    
	    selbtn = makebtn('ファイル選択',80,18);
	    selbtn.x=2;
	    selbtn.y=2;
	    selbtn.addEventListener(MouseEvent.CLICK, browse);
	    addChild(selbtn);
	    uplbtn = makebtn('アップロード',80,18);
	    uplbtn.x=selbtn.x+selbtn.width+5;
	    uplbtn.y=2;
	    uplbtn.addEventListener(MouseEvent.CLICK, upload);
	    uplbtn.hitTestState = null;
	    uplbtn.enabled = true;
	    addChild(uplbtn);

	    messageField.text = "ファイル未選択";
	    messageField.wordWrap=true;
	    messageField.border=true;
	    messageField.borderColor=0xC0C0C0;
	    messageField.width=590;
	    messageField.height=200;
	    messageField.y=selbtn.x+selbtn.height+5;
	    messageField.x=2;
	    addChild(messageField);

            //messageField2.text = "url="+this.root.loaderInfo.parameters["url"];
	    messageField2.wordWrap=true;
	    messageField2.width=590;
	    messageField2.height=40;
	    messageField2.y = messageField.y+messageField.height;
	    messageField2.x=2;
	    addChild(messageField2);

	    req=new URLRequest(this.root.loaderInfo.parameters["url"]);
            req.method=URLRequestMethod.POST;
	    req.data = new URLVariables();
	    req.data.PHPSESSID = this.root.loaderInfo.parameters["sid"];
	    req.data.category = this.root.loaderInfo.parameters["category"];
	    max_filesize = this.root.loaderInfo.parameters["max_filesize"];
	    req.data.multi='upload';
	    req.data.act='upload';
	    //messageField2.appendText("\n"+req.data.toString());
        }
    }
}
