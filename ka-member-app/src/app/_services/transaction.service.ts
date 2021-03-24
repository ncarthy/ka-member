import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { Transaction } from '@app/_models';

const baseUrl = `${environment.apiUrl}/transaction`;

@Injectable({ providedIn: 'root' })
export class TransactionService {
    constructor(private http: HttpClient) { }

    getAll() {
        return this.http.get<Transaction[]>(baseUrl);
    }

    getById(id: number) {
        return this.http.get<Transaction>(`${baseUrl}/${id}`);
    }

    getByMember(idmember: number) {
        return this.http.get<Transaction>(`${baseUrl}/idmember/${idmember}`);
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

    deleteByMember(idmember: number) {
        return this.http.delete(`${baseUrl}/idmember/${idmember}`);
    }
}