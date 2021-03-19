import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { Member } from '@app/_models';

const baseUrl = `${environment.apiUrl}/member`;

@Injectable({ providedIn: 'root' })
export class MemberService {
    constructor(private http: HttpClient) { }

    getAll() {
        return this.http.get<Member[]>(baseUrl);
    }

    getById(id: number) {
        return this.http.get<Member>(`${baseUrl}/${id}`);
    }

    create(params: any) {
        return this.http.post(baseUrl, params);
    }

    update(id: number, params: any) {
        return this.http.put(`${baseUrl}/${id}`, params);
    }

    delete(id: number) {
        return this.http.delete(`${baseUrl}/${id}`);
    }

    anonymize(id: number) {
        return this.http.patch(`${baseUrl}/${id}`, `{"method": "Anonymize"}`);
    }

    setToFormer(id: number) {
        return this.http.patch(`${baseUrl}/${id}`, `{"method": "setToFormer"}`);
    }
}