
function uploadNanogongRecording(recorderid, uploadpage) {
    YUI(M.yui.loader).use('json-parse', 'node', function(Y) {
        var inputfile = Y.one('#filenanoname');
        var filename = (inputfile)?inputfile.get('value'):'';
        var itemid = Y.one('input[name="itemid"]').get('value');

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

        uploadpage += '&title='+encodeURIComponent(filename) + '&itemid='+itemid;
        var ret = recorder.sendGongRequest('PostToForm',
                                            uploadpage,
                                            'repo_voicerecording_file',
                                            '',
                                            filename);
        if (ret == null || ret == '') {
            alert(M.util.get_string('submitfail','repository_voicerecording'));
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
