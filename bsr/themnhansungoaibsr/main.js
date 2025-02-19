const container = document.querySelector('#example1');
const exampleConsole = document.querySelector('#output');
// const load = document.querySelector('#load');
const save = document.querySelector('#id_themnhansungoaibsr_btnsave');

const hot = new Handsontable(container, {
    startRows: 30,
    startCols: 2,
    rowHeaders: true,
    colHeaders: ['Họ và tên', 'CMND/CCCD'],
    height: 'auto',
    width: 'auto',
    stretchH: 'all',
    licenseKey: 'non-commercial-and-evaluation',
    contextMenu: true,
    afterChange: function(change, source) {
        $disablesavebtn = true;
        if (source === 'loadData') {
            return; //don't save this change
        }
//         changes?.forEach(([row, prop, oldValue, newValue]) => {
//             //
//         });
// console.log(change);
// save.disabled = $disablesavebtn;
        if (hot.getData().every((r) => {return r.every((c) => {return c == null})})) {
            save.disabled = true;
        } else {
            save.disabled = false;
        }
    }
});
save.addEventListener('click', () => {
    // save all cell's data
    // fetch('/scripts/json/save.json', {
    //         method: 'POST',
    //         mode: 'no-cors',
    //         headers: {
    //             'Content-Type': 'application/json'
    //         },
    //         body: JSON.stringify({ data: hot.getData() })
    //     })
    //     .then(response => {
    //         exampleConsole.innerText = 'Data saved';
    //         console.log('The POST request is only used here for the demo purposes');
    //     });

    // Add spinner if it not there
    var actionarea = Y.one('#fitem_id_themnhansungoaibsr_btnsave label');
    var spinner = M.util.add_spinner(Y, actionarea);
    // var spinner = M.util.add_lightbox(Y, actionarea);
    var responsetext = {};
    var params = {
        sesskey : M.cfg.sesskey,
        positionid : Number(Y.one('#id_positionid').get('value')),
        organisationid : Number(Y.one('input[name=organisationid]').get('value')),
        courseid : Number(Y.one('input[name=courseid]').get('value')),
        users: hot.getData()
    };
    Y.io(M.cfg.wwwroot+'/bsr/themnhansungoaibsr/save.ajax.php', {
        method: 'POST',
        data: build_querystring(params),
        on: {
                start : function() {
                    $('#output').addClass('hide');
                    spinner.show();
                },
                success: function(tid, response) {
                    try {
                        responsetext = Y.JSON.parse(response.responseText);
                        if (responsetext.error) {
                            Y.use('moodle-core-notification-ajaxexception', function() {
                                return new M.core.ajaxException(responsetext).show();
                            });
                        } else if (responsetext.success) {
                            // todo
                            console.log(responsetext.members);
                            $('#output').text('Đã thêm ' + responsetext.members.length + ' nhân sự');
                            $('#output').removeClass('hide');
                            hot.clear();
                        }
                    } catch (e) {}
                    if (spinner) {
                        spinner.hide();
                    }
                },
                failure : function(tid, response) {
                    if (spinner) {
                        spinner.hide();
                    }
                    Y.use('moodle-core-notification-ajaxexception', function() {
                        return new M.core.ajaxException(response).show();
                    });
                }
            },
        context: this
    });
});