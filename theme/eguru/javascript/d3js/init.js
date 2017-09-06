function start_radialProgress(Y, groupid, groupname, value, maxvalue) {

    var rp = radialProgress(document.getElementById('radialprogress' + groupid))
        .label(groupname)
        .diameter(100)
        .minValue(0)
        .maxValue(maxvalue)
        .value(value)
        .render();

    /*
     Y.each(Y.all('.radialprogress'), function(value, index) {
     var rp = radialProgress(document.getElementById('radialprogress'+index))
     .label('Radial')
     .diameter(120)
     .minValue(0)
     .maxValue(value.getAttribute('data-maxvalue'))
     .value(value.getAttribute('data-value'))
     .render();

     });
     */
}
/**
 * Created by lcohen on 15/02/2016.
 */
