

/// Represents a single container entry in the dynamic list.
/// Immutable value object — use [copyWith] to create updated instances.
class ContainerEntry {
  final String? containerType;
  final String? containerSize;
  final String? containerNumber;

  ContainerEntry({
    this.containerType,
    this.containerSize,
    this.containerNumber,
  });

  ContainerEntry copyWith({
    String? containerType,
    String? containerSize,
    String? containerNumber,
  }) {
    return ContainerEntry(
      containerType: containerType ?? this.containerType,
      containerSize: containerSize ?? this.containerSize,
      containerNumber: containerNumber ?? this.containerNumber,
    );
  }

  /// Validates that all required fields are filled.
  bool get isValid =>
      containerSize != null &&
      (containerNumber?.isNotEmpty ?? false);

  /// Returns a map for API serialization.
  Map<String, dynamic> toJson() => {
    'container_type': containerType,
    'container_size': containerSize,
    'container_number': containerNumber,
  };

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is ContainerEntry &&
          runtimeType == other.runtimeType &&
          containerType == other.containerType &&
          containerSize == other.containerSize &&
          containerNumber == other.containerNumber;

  @override
  int get hashCode =>
      containerType.hashCode ^
      containerSize.hashCode ^
      containerNumber.hashCode;
}
