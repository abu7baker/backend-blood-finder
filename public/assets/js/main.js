/**
 * Blood Finder - Admin Panel Sidebar Control
 * Sidebar starts CLOSED by default
 */

document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.getElementById("sidebar");
    const sidebarToggle = document.getElementById("sidebarToggle");
    const sidebarClose = document.getElementById("sidebarClose");
    const sidebarOverlay = document.getElementById("sidebarOverlay");
    const mainContent = document.getElementById("mainContent");

    // --- Start Closed Always ---
    sidebar.classList.add("closed");
    mainContent.classList.add("expanded");

    // --- Toggle Sidebar ---
    sidebarToggle?.addEventListener("click", () => {
        const isClosed = sidebar.classList.contains("closed");

        if (isClosed) {
            openSidebar();
        } else {
            closeSidebar();
        }
    });

    // --- Close Sidebar when clicking X button or overlay ---
    sidebarClose?.addEventListener("click", closeSidebar);
    sidebarOverlay?.addEventListener("click", closeSidebar);

    // --- Auto behavior when resizing ---
    window.addEventListener("resize", () => {
        if (window.innerWidth < 992) {
            closeSidebar(true);
        } else {
            sidebarOverlay.classList.remove("active");
        }
    });

    // --- Functions ---
   function openSidebar() {
    sidebar.classList.remove("closed");
    sidebar.classList.add("active");

    // أضف هذا:
    document.body.classList.add("sidebar-open");

    if (window.innerWidth < 100) {
        sidebarOverlay.classList.add("active");
    }
}

    function closeSidebar(force = false) {
    sidebar.classList.add("closed");
    sidebar.classList.remove("active");
    
    // أضف هذا:
    document.body.classList.remove("sidebar-open");

    sidebarOverlay.classList.remove("active");
}

});
/*-------------------------------------
    Sidebar Toggle
-------------------------------------*/
// const sidebarToggle = document.getElementById("sidebarToggle");
// const sidebar = document.getElementById("sidebar");
// const sidebarOverlay = document.getElementById("sidebarOverlay");

// if (sidebarToggle) {
//     sidebarToggle.addEventListener("click", () => {
//         sidebar.classList.toggle("open");
//         sidebarOverlay.classList.toggle("active");
//     });
// }

// if (sidebarOverlay) {
//     sidebarOverlay.addEventListener("click", () => {
//         sidebar.classList.remove("open");
//         sidebarOverlay.classList.remove("active");
//     });
// }


/*-------------------------------------
    Search Users (Client Side)
-------------------------------------*/
const searchInput = document.getElementById("searchUsers");
const usersTable = document.getElementById("usersTable");

if (searchInput) {
    searchInput.addEventListener("keyup", function () {
        const filter = this.value.toLowerCase();
        const rows = usersTable.querySelectorAll("tbody tr");

        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? "" : "none";
        });
    });
}

/*-------------------------------------
    Filter by Status
-------------------------------------*/
const filterStatus = document.getElementById("filterStatus");

if (filterStatus) {
    filterStatus.addEventListener("change", function () {
        filterUsers();
    });
}

/*-------------------------------------
    Filter by Type (Role)
-------------------------------------*/
const filterType = document.getElementById("filterType");

if (filterType) {
    filterType.addEventListener("change", function () {
        filterUsers();
    });
}

/*-------------------------------------
    Filter Function
-------------------------------------*/
function filterUsers() {
    const statusValue = filterStatus.value;
    const typeValue = filterType.value;
    const rows = usersTable.querySelectorAll("tbody tr");

    rows.forEach(row => {
        const rowStatus = row.querySelector("td:nth-child(6)").innerText.trim();
        const rowType = row.querySelector("td:nth-child(5)").innerText.trim();

        let statusMatch = statusValue === "all" || rowStatus.includes(statusValue);
        let typeMatch = typeValue === "all" || rowType.includes(typeValue);

        row.style.display = statusMatch && typeMatch ? "" : "none";
    });
}

/*-------------------------------------
    Export Table to CSV
-------------------------------------*/
function exportUsersCSV() {
    let csv = [];
    const rows = document.querySelectorAll("#usersTable tr");

    rows.forEach(row => {
        const cols = row.querySelectorAll("td, th");
        const rowData = [];

        cols.forEach(col => rowData.push(col.innerText.replace(/,/g, "")));

        csv.push(rowData.join(","));
    });

    const blob = new Blob([csv.join("\n")], { type: "text/csv" });
    const link = document.createElement("a");

    link.href = URL.createObjectURL(blob);
    link.download = "users.csv";
    link.click();
}

/*-------------------------------------
    View User Modal AJAX
-------------------------------------*/
function viewUser(id) {
    fetch(`/admin/users/${id}/json`)
        .then(response => {
            if (!response.ok) {
                throw new Error('خطأ في جلب بيانات المستخدم');
            }
            return response.json();
        })
        .then(user => {
            // تعبئة البيانات في المودال
            document.getElementById('viewUserAvatar').innerText =
                user.full_name ? user.full_name.charAt(0) : '?';

            document.getElementById('viewUserName').innerText = user.full_name || '';
            document.getElementById('viewUserEmail').innerText = user.email || '—';
            document.getElementById('viewUserPhone').innerText = user.phone || '—';
            document.getElementById('viewUserCity').innerText = user.city || '—';
            document.getElementById('viewUserBlood').innerText = user.blood_type || '—';
            document.getElementById('viewUserCreated').innerText = user.created_at || '—';

            // الحالة
            const statusSpan = document.getElementById('viewUserStatus');
            statusSpan.className = 'status-badge';
            if (user.status === 'active') {
                statusSpan.classList.add('status-active');
                statusSpan.innerText = 'نشط';
            } else if (user.status === 'pending') {
                statusSpan.classList.add('status-pending');
                statusSpan.innerText = 'قيد الانتظار';
            } else {
                statusSpan.classList.add('status-blocked');
                statusSpan.innerText = 'محظور';
            }

            // النوع (الدور)
            const roleMap = {
                admin: 'مدير',
                hospital: 'مستشفى',
                user: 'مستخدم'
            };
            document.getElementById('viewUserRole').innerText =
                roleMap[user.role_name] || user.role_name || '—';

            // إظهار المودال
            const modal = new bootstrap.Modal(document.getElementById('viewUserModal'));
            modal.show();
        })
        .catch(err => {
            console.error(err);
            alert('حدث خطأ أثناء جلب بيانات المستخدم');
        });
}






