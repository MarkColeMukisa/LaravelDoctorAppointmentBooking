<?php

namespace App\Http\Controllers;

use App\Models\PatientStatusChangeRequest;

class AdminController extends Controller
{
    public function loadAdminDashboard()
    {
        return view('admin.dashboard');
    }

    public function loadDoctorListing()
    {
        return view('admin.doctor-listing');
    }

    public function loadDoctorApplications()
    {
        return view('admin.doctor-applications');
    }

    public function loadDoctorApplicationDetail($applicationId)
    {
        return view('admin.doctor-application-detail', compact('applicationId'));
    }

    public function loadDoctorForm()
    {
        return view('admin.doctor-form');
    }

    public function loadEditDoctorForm($doctor_id)
    {
        $id = $doctor_id;

        return view('admin.edit-doctor', compact('id'));
    }

    public function loadAllSpecialities()
    {
        return view('admin.specialities');
    }

    public function loadSpecialityForm()
    {
        return view('admin.speciality-form');
    }

    public function loadEditSpecialityForm($speciality_id)
    {
        $id = $speciality_id;

        return view('admin.edit-speciality-form', compact('id'));
    }

    public function loadAllAppointments()
    {
        return view('admin.appointments');
    }

    public function loadPatientRecords()
    {
        return view('admin.patients');
    }

    public function loadAnnouncementBanners()
    {
        return view('admin.announcements');
    }

    public function printPatientStatusAudits()
    {
        $decisions = PatientStatusChangeRequest::query()
            ->with([
                'patient:id,name,email',
                'admin:id,name',
                'doctor.doctorUser:id,name',
            ])
            ->whereIn('status', [
                PatientStatusChangeRequest::STATUS_APPROVED,
                PatientStatusChangeRequest::STATUS_REJECTED,
            ])
            ->latest('decided_at')
            ->limit(2000)
            ->get();

        return view('admin.patient-status-audits-print', compact('decisions'));
    }

    public function loadReschedulingForm($id)
    {
        $appointment_id = $id;

        return view('admin.reschedule-form', compact('appointment_id'));
    }
}
