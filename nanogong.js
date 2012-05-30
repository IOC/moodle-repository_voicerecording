//  Voice recording repository plugin for Moodle
//  Copyright © 2012  Institut Obert de Catalunya
//  
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, either version 3 of the License, or
//  (at your option) any later version.
//  
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//  
//  You should have received a copy of the GNU General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.


function uploadNanogongRecording(recorderid, uploadpage, clientid) {
	YUI(M.yui.loader).use('json-parse', 'node', function(Y) {
		var inputfile = Y.one('#filenanoname');
		var filename = (inputfile)?inputfile.get('value'):'';
		
	    var recorder = document.getElementById(recorderid);
	    if (recorder == null) {
	        alert(M.util.get_string('norecorder','repository_voicerecording'));
	        return false;
	    }
	    
	    var duration = parseInt(recorder.sendGongRequest('GetMediaDuration', 'audio')) || 0;
	    if (duration <= 0) {
	        alert(M.util.get_string('norecording','repository_voicerecording'));
	        return false;
	    }
		
		if (filename == '' || filename.replace(/\s/g,'') == '') {
            inputfile.focus();
            alert(M.util.get_string('nofilename','repository_voicerecording'));
            return false;
        }

	    uploadpage += '&title='+encodeURIComponent(filename);
	    var ret = recorder.sendGongRequest('PostToForm',
	                                        uploadpage,
	                                        'repo_voicerecording_file',
	                                        '',
	                                        filename);
		//Get preview id
		var id = Y.one('div.clearlooks2').getAttribute('id');
	    if (ret == null || ret == '') {
	        alert(M.util.get_string('submitfail','repository_voicerecording'));
	    }else{
	    	var data = Y.JSON.parse(ret);
	    	var frame = Y.Node.getDOMNode(Y.one('#' + id + '_ifr'));
	    	YUI({
	    	    win: frame.contentWindow,
	    	    doc: frame.contentWindow.document
	    	}).use('node', function(Frame) {
	    	    //Get elements
		    var url = (data.url?data.url:data.newfile.url);
		    var file = Frame.one('#filename');
		    var source = Frame.one('#src');
		    var prev = Frame.one('#prev');
		    //Set url
		    source.set('value', url);
		    //Set filename
		    file.set('value', url);
		    //Set preview
		    prev.set('innerHTML', embed_nanogong(url));
		    //Close filepicker
		    M.core_filepicker.instances[clientid].hide();
	    	});
	    }
	});
}

/**
 * 
 * @param url
 * @returns {String}
 */
function embed_nanogong(url){
	var html='<object type="application/x-java-applet" width="120" height="40">'+
    		 '<param name="code" value="gong.NanoGong"/>'+
    		 '<param name="archive" value="' + M.cfg.wwwroot + '/repository/voicerecording/nanogong.jar"/>'+
    		 '<param name="SoundFileURL" value="' + url + '" />'+	
    		 '<param name="ShowRecordButton" value="false" />'+
    		 '<param name="ShowSaveButton" value="false" />';
    		 '</object>';
   	return html;
}
