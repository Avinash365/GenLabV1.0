@extends('superadmin.layouts.app')
@section('title', 'Create New User')
@section('content')

<div class="row">
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-header justify-content-between">
                                    <div class="card-title">
                                        Calibration Add
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Agency Name</label>
                                            <input type="text" class="form-control" placeholder="First Name">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Equipment Name</label>
                                            <input type="text" class="form-control" placeholder="Last Name">
                                        </div>
                                    
                                        <div class="col-md-6 mb-3">
                                            <div class="row">
                                                <div class="col-xl-12 mb-3">
                                                    <label class="form-label">Issue Date</label>
                                                    <input type="date" class="form-control">
                                                </div>
                                                <div class="col-xl-12 mb-3">
                                                    <label class="form-label">Expire Date</label>
                                                    <input type="date" class="form-control">
                                                </div>
                                                
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-12">
                                            <button type="submit" class="btn btn-primary">Add</button>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                        
                    </div>

@endsection