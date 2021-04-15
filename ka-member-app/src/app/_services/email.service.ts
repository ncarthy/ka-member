import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';

const baseUrl = `${environment.apiUrl}/email`;

@Injectable({ providedIn: 'root' })
export class EmailService {
    constructor(private http: HttpClient) { }

    prepareReminderEmail(params: any) {
        return this.http.post<string>(`${baseUrl}/prepare_reminder`, params);
    }

}