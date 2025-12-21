// document.addEventListener("DOMContentLoaded", () => {

//     // ============================
//     //  View Hospital Modal
//     // ============================
//     window.viewHospital = function(id) {

//         fetch(`/admin/hospitals/${id}/json`)
//             .then(res => {
//                 if (!res.ok) throw new Error("فشل في جلب بيانات المستشفى");
//                 return res.json();
//             })
//             .then(h => {

//                 document.getElementById('viewHospitalName').innerText  = h.name;
//                 document.getElementById('viewHospitalCity').innerText  = h.city;
//                 document.getElementById('viewHospitalEmail').innerText = h.user.email;
//                 document.getElementById('viewHospitalPhone').innerText = h.user.phone;
//                 document.getElementById('viewHospitalBeds').innerText  = h.location ?? '—';
//                 document.getElementById('viewHospitalStatus').innerText =
//                     h.verified === 'verified' ? 'نشط' :
//                     h.verified === 'pending'  ? 'قيد المراجعة' : 'محظور';
//                 document.getElementById('viewHospitalCreated').innerText = h.created_at;

//                 new bootstrap.Modal(
//                     document.getElementById('viewHospitalModal')
//                 ).show();
//             })
//             .catch(err => console.error("JSON Error:", err));
//     };


//     // ============================
//     //  Edit Hospital Modal
//     // ============================
//     window.editHospital = function(id) {

//         fetch(`/admin/hospitals/${id}/json`)
//             .then(res => {
//                 if (!res.ok) throw new Error("فشل في جلب بيانات المستشفى");
//                 return res.json();
//             })
//             .then(h => {

//                 document.getElementById('edit_id').value             = id;
//                 document.getElementById('edit_hospital_name').value  = h.name;
//                 document.getElementById('edit_city').value           = h.city;
//                 document.getElementById('edit_email').value          = h.user.email;
//                 document.getElementById('edit_phone').value          = h.user.phone;
//                 document.getElementById('edit_location').value       = h.location;
//                 document.getElementById('edit_status').value         = h.verified;

//                 document.getElementById('editHospitalForm').action =
//                     `/admin/hospitals/${id}`;

//                 new bootstrap.Modal(
//                     document.getElementById('editHospitalModal')
//                 ).show();
//             })
//             .catch(err => console.error("JSON Error:", err));
//     };

// });
