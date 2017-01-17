function loadActivities() {
    var container = $("#user-activities");
    if (container.length > 0) {
        $.ajax({
            url : baseUrl + "activity/load?limit=" + container.data("limit"),
            success : function(data) {
                container.html(data);
                reloadInits()
            }
        })
    }
}
addPageHook("loadActivities");
$(function() {
   loadActivities();
});