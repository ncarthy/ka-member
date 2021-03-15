import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { MembershipStatus } from '@app/_models';

const baseUrl = `${environment.apiUrl}/status`;

@Injectable({ providedIn: 'root' })
export class MembershipStatusService {
    constructor(private http: HttpClient) { }

    getAll() {
        return this.http.get<MembershipStatus[]>(baseUrl);
    }

    getById(id: number) {
        return this.http.get<MembershipStatus>(`${baseUrl}/${id}`);
    }

}