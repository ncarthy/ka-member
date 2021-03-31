import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { MemberCountResponse } from '@app/_models';

const baseUrl = `${environment.apiUrl}/members`;

@Injectable({ providedIn: 'root' })
export class MembersService {
    constructor(private http: HttpClient) { }

    getSummary() {
        return this.http.get<MemberCountResponse>(`${baseUrl}/summary`);
    }

    getMailingList() {
        return this.http.get<MemberCountResponse>(`${baseUrl}/mailinglist`);
    }

    getEmailList() {
        return this.http.get<MemberCountResponse>(`${baseUrl}/emaillist`);
    }
}