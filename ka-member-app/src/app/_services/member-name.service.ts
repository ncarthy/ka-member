import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { MemberName } from '@app/_models';

const baseUrl = `${environment.apiUrl}/name/idmember`;

@Injectable({ providedIn: 'root' })
export class MemberNameService {
    constructor(private http: HttpClient) { }


    getAllForMember(idmember: number) {
        return this.http.get<MemberName[]>(`${baseUrl}/${idmember}`);
    }

    updateAllForMember(idmember: number, names: MemberName[]) {
        return this.http.put(`${baseUrl}/${idmember}`, names);
    }

    deleteAllForMember(idmember: number) {
        return this.http.delete(`${baseUrl}/${idmember}`);
    }
}