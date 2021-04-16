import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';

const baseUrl = `${environment.apiUrl}/email`;

@Injectable({ providedIn: 'root' })
export class EmailService {
    constructor(private http: HttpClient) { }

    prepareReminderEmail(params: any) {
        return this.http.post<any>(`${baseUrl}/prepare_reminder`, params);
    }

    sendReminderEmail(params: any) {
        return this.http.post<any>(`${baseUrl}/send_reminder`, params);
    }

    prepareSwitchRequestEmail(params: any) {
        return this.http.post<any>(`${baseUrl}/prepare_switchrequest`, params);
    }

    sendSwitchRequestEmail(params: any) {
        return this.http.post<any>(`${baseUrl}/send_switchrequest`, params);
    }

}