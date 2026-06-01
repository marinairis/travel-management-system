<?php

namespace Database\Seeders;

use App\Models\TravelRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TravelRequestSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $manager = User::where('role', 'manager')->first();
        $requesters = User::where('role', 'requester')->get();

        $now = Carbon::now();

        $destinations = [
            'São Paulo, SP', 'Rio de Janeiro, RJ', 'Belo Horizonte, MG', 'Curitiba, PR',
            'Salvador, BA', 'Fortaleza, CE', 'Brasília, DF', 'Porto Alegre, RS',
            'Recife, PE', 'Manaus, AM', 'Goiânia, GO', 'Vitória, ES', 'Florianópolis, SC',
            'Natal, RN', 'João Pessoa, PB', 'Aracaju, SE', 'Maceió, AL', 'Teresina, PI',
            'São Luís, MA', 'Campo Grande, MS', 'Cuiabá, MT', 'Palmas, TO', 'Boa Vista, RR',
            'Rio Branco, AC', 'Porto Velho, RO', 'Macapá, AP', 'Belém, PA', 'Marabá, PA',
        ];

        $notes = [
            'Reunião com cliente', 'Workshop de produto', 'Conferência de tecnologia',
            'Visita a fornecedor', 'Apresentação para investidores', 'Treinamento regional',
            'Expansão de operações', 'Negciação de contrato', 'Visita técnica',
            'Participação em evento', 'Reunião estratégica', 'Auditoria',
            null, null, null, null, null,
        ];

        $statuses = ['requested', 'requested', 'requested', 'approved', 'approved', 'cancelled'];

        $travelTypes = ['bus', 'plane', 'car', 'hotel'];

        $requests = [];

        // Generate 50 travel requests
        for ($i = 0; $i < 50; $i++) {
            $requester = $requesters->random();
            $destination = $destinations[array_rand($destinations)];
            $departureDays = rand(-10, 90);
            $returnDays = $departureDays + rand(2, 7);
            $status = $statuses[array_rand($statuses)];
            $note = $notes[array_rand($notes)];
            $travelType = $travelTypes[array_rand($travelTypes)];

            $departureDate = $now->copy()->addDays($departureDays);
            $returnDate = $now->copy()->addDays($returnDays);

            $approvedBy = null;
            $approvedAt = null;
            if ($status === 'approved') {
                $approvers = [$admin, $manager];
                $approvedBy = $approvers[array_rand($approvers)]->id;
                $approvedAt = $now->copy()->subDays(rand(1, 5));
            }

            $requests[] = [
                'user_id' => $requester->id,
                'requester_name' => $requester->name,
                'destination' => $destination,
                'departure_date' => $departureDate->format('Y-m-d'),
                'return_date' => $returnDate->format('Y-m-d'),
                'status' => $status,
                'approved_by' => $approvedBy,
                'approved_at' => $approvedAt,
                'notes' => $note,
                'travel_type' => $travelType,
            ];
        }

        foreach ($requests as $data) {
            TravelRequest::create($data);
        }
    }
}
