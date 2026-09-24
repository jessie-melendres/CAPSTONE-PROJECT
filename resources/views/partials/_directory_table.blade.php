{{-- Generic directory table. $columns = ['label1', 'label2', ...]; $rows = [['cells' => [...], 'editUrl' => ?, 'deleteUrl' => ?], ...] --}}
<div class="directory-table-wrap">
    <table class="directory-table">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th>{{ $column }}</th>
                @endforeach
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row['cells'] as $cell)
                        <td>{!! $cell !!}</td>
                    @endforeach
                    <td class="table-actions">
                        @if (!empty($row['editUrl']))
                            <a href="{{ $row['editUrl'] }}"><button type="button">Edit</button></a>
                        @endif
                        @if (!empty($row['deleteUrl']))
                            <form method="POST" action="{{ $row['deleteUrl'] }}" onsubmit="return confirm('Remove this record?');" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Remove</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ count($columns) + 1 }}">No records yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
